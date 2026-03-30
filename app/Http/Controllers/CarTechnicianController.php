<?php

namespace App\Http\Controllers;

use App\Models\CarTechnician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\ApiResponseTrait;
class CarTechnicianController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of technicians.
     */
    public function index(Request $request)
    {
        $query = CarTechnician::query();

        if (Auth::user()->can('get all companies')) {
            $query->with('company');
        } else {
            $query->where('company_id', Auth::user()->company_id);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        return $this->successResponse($query->get(),'Got data successfully',200);
    }

    /**
     * Store a new technician.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Force company_id before validation
        $companyId = $user->company_id ?? $request->company_id;

        $validated = $request->validate([
            'company_id' => [

                'exists:companies,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('car_technicians')->where(function ($q) use ($companyId) {
                    return $q->where('company_id', $companyId);
                }),
            ],
        ]);

        // overwrite to ensure consistency
        $validated['company_id'] = $companyId;

        DB::beginTransaction();
        try {
            $technician = CarTechnician::create($validated);

            DB::commit();
            return $this->successResponse($technician, 'Created Successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update technician (only name can be updated).
     */
    public function update(Request $request, CarTechnician $carTechnician)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('car_technicians')
                    ->ignore($carTechnician->id)
                    ->where(function ($q) use ($carTechnician) {
                        return $q->where('company_id', $carTechnician->company_id);
                    }),
            ],
        ]);

        DB::beginTransaction();
        try {
            $carTechnician->update(['name' => $validated['name']]);

            DB::commit();
            return $this->successResponse($carTechnician->fresh(), 'Car technician updated successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse( $e->getMessage(),500);
        }
    }

    /**
     * Delete a technician (only if not related to a car).
     */
    public function destroy(CarTechnician $carTechnician)
    {
        DB::beginTransaction();
        try {
//            if ($carTechnician->cars()->exists()) {
//                DB::rollBack();
//                return $this->errorResponse('Technician cannot be deleted because it is assigned to cars.', 400);
//            }

            $carTechnician->delete();

            DB::commit();
            return $this->successResponse([],'Technician deleted successfully',200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse( $e->getMessage(),500);
        }
    }
}

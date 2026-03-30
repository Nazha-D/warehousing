<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequests\StoreCarRequest;

use App\Http\Requests\CarRequests\UpdateCarRequest;
use App\Models\Car;
use App\Models\Client;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarColor;
use App\Models\CarTechnician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
class CarController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = Car::with(['client', 'carBrand', 'carModel', 'carColor', 'carTechnician'])
            ->whereHas('client', fn($q) => $q->where('company_id', $companyId));

        if ($search = $request->input('search')) {
            $query->where('plate_number', 'like', "%$search%")
                ->orWhere('chassis_number', 'like', "%$search%");
        }
        if ($isPaginated = $request->input('isPaginated')) {
            $query->paginate($request->input('perPage'));
        }
        return $this->successResponse($query->get(),'got cars successfully',200);
    }


    public function create()
    {
        $companyId = Auth::user()->company_id;
     $data=[
     //    'clients'     => Client::where('company_id', $companyId)->get(),
         'car_brands'  => CarBrand::all(),
         'car_models'  => CarModel::all(),
         'car_colors'  => CarColor::all(),
         'technicians' => CarTechnician::where('company_id', $companyId)->get(),
         'ratings'     => ['VIP', 'Regular', 'Blocked'],
     ];
        return $this->successResponse($data,'Got data needed for car creation successfully',200);
    }


    public function store(StoreCarRequest $request)
    {
        DB::beginTransaction();
        try {
            $car = Car::create(
[
                'client_id'=>$request->clientId,
                'car_brand_id'=>$request->carBrandId,
                'car_model_id'=>$request->carModelId,
                'car_color_id'=>$request->carColorId,
                'car_technician_id'=>$request->carTechnicianId,
                'plate_number'=>$request->plateNumber,
                'chassis_number'=>$request->chassisNumber,
                'car_fax'=>$request->carFax,
                'year'=>$request->year,
                'rating'=>$request->rating,
                'odometer'=>$request->odometer,
                'comment'=>$request->comment,
             ]
            );
            DB::commit();
            return $this->successResponse($car, 'Car created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create car', 500, $e->getMessage());
        }
    }

    /**
     * Show a single car.
     */
    public function show(Car $car)
    {
        $this->authorizeCompany($car);
        return $this->successResponse($car->load(['client', 'carBrand', 'carModel', 'carColor', 'carTechnician']));
    }

    /**
     * Update a car.
     */
    public function update(UpdateCarRequest $request, Car $car)
    {
        $this->authorizeCompany($car);

        DB::beginTransaction();
        try {
            $car->update($request->validated());
            DB::commit();
            return $this->successResponse($car->fresh(), 'Car updated successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update car', 500, $e->getMessage());
        }
    }

    /**
     * Delete a car.
     */
    public function destroy(Car $car)
    {
        $this->authorizeCompany($car);

        DB::beginTransaction();
        try {
            $car->delete();
            DB::commit();
            return $this->successResponse('Car deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete car', 500, $e->getMessage());
        }
    }


    protected function authorizeCompany(Car $car)
    {
        if ($car->client->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized');
        }
    }
}

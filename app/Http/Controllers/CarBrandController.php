<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Traits\ApiResponseTrait;
class CarBrandController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        $query = CarBrand::query();

        // Search by name if ?search= provided
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        }

        $brands = $query->with('carModels')->get();

        return $this->successResponse($brands, 'Brands retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[\pL\s]+$/u', // letters + spaces only
            ],
        ]);

        DB::beginTransaction();

        try {
            $brand = CarBrand::addBrand($request->name, auth()->id());

            DB::commit();

            return $this->successResponse($brand, 'Brand created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create brand', 500, $e->getMessage());
        }
    }

    public function update(Request $request, CarBrand $carBrand)
    {
        // Prevent updating system-wide brand
        if (is_null($carBrand->user_id)) {
            return $this->errorResponse("This brand is a system-wide default and cannot be modified", 403);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[\pL\s]+$/u',
            ],
        ]);

        DB::beginTransaction();

        try {
            $carBrand->update([
                'name' => ucfirst(strtolower(trim($request->name))),
            ]);

            DB::commit();

            return $this->successResponse($carBrand, 'Brand updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update brand', 500, $e->getMessage());
        }
    }

    public function destroy(CarBrand $carBrand)
    {
        // Prevent deleting system-wide brand
        if (is_null($carBrand->user_id)) {
            return $this->errorResponse("This brand is a system-wide default and cannot be deleted", 403);
        }

        // Allow only owner to delete
        if ($carBrand->user_id !== auth()->id()) {
            return $this->errorResponse("You cannot delete a brand created by another user", 403);
        }

        DB::beginTransaction();

        try {
            $carBrand->delete();

            DB::commit();

            return $this->successResponse(null, 'Brand deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete brand', 500, $e->getMessage());
        }
    }
}

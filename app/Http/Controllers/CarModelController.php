<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Traits\ApiResponseTrait;
class CarModelController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        $query = CarModel::with('brand');


        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $models = $query->get();

        return $this->successResponse($models, 'Got car models successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'car_brand_id' => 'required|exists:car_brands,id',
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[\pL\s]+$/u',
            ],
        ]);

        DB::beginTransaction();

        try {
            $model = CarModel::addModel(
                $request->car_brand_id,
                $request->name,
                auth('sanctum')->id()
            );

            DB::commit();

            return $this->successResponse($model, 'Car model created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create car model', 500, $e->getMessage());
        }
    }

    public function update(Request $request, CarModel $carModel)
    {

        if (is_null($carModel->user_id)) {
            return $this->errorResponse("This model is a system-wide default and cannot be modified", 403);
        }

        if ($carModel->user_id !== auth('sanctum')->id()) {
            return $this->errorResponse("You cannot update a model created by another user", 403);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[\pL\s]+$/u',
            ],
        ]);

        DB::beginTransaction();

        try {
            $name = ucfirst(strtolower(trim($request->name)));

            $exists = CarModel::where('car_brand_id', $carModel->car_brand_id)
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->where('id', '!=', $carModel->id)
                ->exists();

            if ($exists) {
                return $this->errorResponse('Model already exists for this brand', 422);
            }

            $carModel->update(['name' => $name]);

            DB::commit();

            return $this->successResponse($carModel, 'Car model updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update car model', 500, $e->getMessage());
        }
    }

    public function destroy(CarModel $carModel)
    {

        if (is_null($carModel->user_id)) {
            return $this->errorResponse("This model is a system-wide default and cannot be modified", 403);
        }

        if ($carModel->user_id !== auth('sanctum')->id()) {
            return $this->errorResponse("You cannot delete a model created by another user", 403);
        }

        DB::beginTransaction();

        try {
            $carModel->delete();

            DB::commit();

            return $this->successResponse(null, 'Car model deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete car model', 500, $e->getMessage());
        }
    }

}

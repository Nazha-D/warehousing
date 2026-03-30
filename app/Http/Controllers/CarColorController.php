<?php

namespace App\Http\Controllers;

use App\Models\CarColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Traits\ApiResponseTrait;
class CarColorController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = CarColor::query();

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where('name', 'LIKE', "%{$search}%");
            }

            $colors = $query->orderBy('name','ASC')->get();

            return $this->successResponse($colors,'Got colors successfully',200);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch colors', 500, $e->getMessage());
        }
    }


    public function store(Request $request)
    {
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
            $color = CarColor::addColor($request->name, auth()->user()->id);

            DB::commit();

            return $this->successResponse($color, 'Color created successfully');
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 422); // Client error
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create color', 500, $e->getMessage());
        }
    }


    public function show(CarColor $carColor)
    {
        try {
            return $this->successResponse($carColor);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch color', 500, $e->getMessage());
        }
    }


    public function update(Request $request, CarColor $carColor)
    {
        if($carColor->user_id!=auth()->user()->id)
        {
            return $this->errorResponse('This color is a system-wide default and cannot be modified', 403);
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
            $name = ucfirst(strtolower(trim($request->name)));


            $exists = CarColor::whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->where('id', '<>', $carColor->id)
                ->first();

            if ($exists) {
                return $this->errorResponse('Color already exists', 422);
            }

            $carColor->update(['name' => $name]);

            DB::commit();

            return $this->successResponse($carColor, 'Color updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update color', 500, $e->getMessage());
        }
    }


    public function destroy(CarColor $carColor)
    {
        DB::beginTransaction();

        try {

            if (is_null($carColor->user_id)) {
                return $this->errorResponse('This is a global color and cannot be deleted', 403);
            }


            if ($carColor->user_id !== auth()->id()) {
                return $this->errorResponse('You are not allowed to delete this color', 403);
            }


            $carColor->delete();

            DB::commit();

            return $this->successResponse(null, 'Color deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete color', 500, $e->getMessage());
        }
    }
}

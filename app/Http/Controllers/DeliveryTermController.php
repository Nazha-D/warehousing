<?php

namespace App\Http\Controllers;

use App\Models\DeliveryTerm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\ApiResponseTrait;
class DeliveryTermController extends Controller
{
    use ApiResponseTrait;
    public function index()
    {
        try {
            $user = auth()->user();

            $deliveryTerms = DeliveryTerm::where('company_id', $user->company_id)->get();

            return $this->successResponse($deliveryTerms, 'Delivery terms fetched successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            $validated = $request->validate([
                'name' => ['required'],
                'code' => [
                    'required',
                    Rule::unique('delivery_terms', 'code')
                        ->where(fn($q) => $q->where('company_id', $user->company_id)),
                ],
            ]);

            $deliveryTerm = DeliveryTerm::create([
                'company_id' => $user->company_id,
                'name'       => $validated['name'],
                'code'       => $validated['code'],
            ]);

            DB::commit();

            return $this->successResponse($deliveryTerm, 'Delivery term created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $user = auth()->user();

            $deliveryTerm = DeliveryTerm::where('company_id', $user->company_id)->find($id);

            if (!$deliveryTerm) {
                return $this->errorResponse('Delivery term not found', Response::HTTP_NOT_FOUND);
            }

            return $this->successResponse($deliveryTerm, 'Delivery term fetched successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $deliveryTerm = DeliveryTerm::where('company_id', $user->company_id)->find($id);

            if (!$deliveryTerm) {
                return $this->errorResponse('Delivery term not found', Response::HTTP_NOT_FOUND);
            }

            $validated = $request->validate([
                'name' => ['sometimes', 'required'],
                'code' => [
                    'sometimes',
                    'required',
                    Rule::unique('delivery_terms', 'code')
                        ->ignore($deliveryTerm->id)
                        ->where(fn($q) => $q->where('company_id', $user->company_id)),
                ],
            ]);

            $deliveryTerm->update($validated);

            DB::commit();

            return $this->successResponse($deliveryTerm, 'Delivery term updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $deliveryTerm = DeliveryTerm::where('company_id', $user->company_id)->find($id);

            if (!$deliveryTerm) {
                return $this->errorResponse('Delivery term not found', Response::HTTP_NOT_FOUND);
            }

            $deliveryTerm->delete();

            DB::commit();

            return $this->successResponse([], 'Delivery term deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

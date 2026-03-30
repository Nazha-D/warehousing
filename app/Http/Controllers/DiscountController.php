<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponseTrait;

class DiscountController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $discounts = Discount::where('company_id', $companyId)->get();

        return $this->successResponse($discounts, 'Discounts retrieved successfully');
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'type'  => 'required|string',
            'value' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $discount = Discount::create([
                'company_id' => $companyId,
                'type'       => $validated['type'],
                'value'      => $validated['value'],
            ]);

            DB::commit();

            return $this->successResponse($discount, 'Discount created successfully');

        } catch (\Throwable $e) {

            DB::rollBack();

            return $this->errorResponse('Failed to create discount', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $companyId = $request->user()->company_id;

        $discount = Discount::where('company_id', $companyId)
            ->where('id', $id)
            ->first();

        if (!$discount) {
            return $this->errorResponse('Discount not found', 404);
        }

        return $this->successResponse($discount, 'Discount retrieved successfully');
    }

    public function update(Request $request,Discount $discount)
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'type'  => 'sometimes|string',
            'value' => 'sometimes|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();



            if (!$discount) {
                DB::rollBack();
                return $this->errorResponse('Discount not found', 404);
            }

            $discount->update($validated);

            DB::commit();

            return $this->successResponse($discount, 'Discount updated successfully');

        } catch (\Throwable $e) {

            DB::rollBack();

            return $this->errorResponse('Failed to update discount', 500);
        }
    }

    public function destroy(Request $request,Discount $discount)
    {
        $companyId = $request->user()->company_id;

        try {
            DB::beginTransaction();



            if (!$discount) {
                DB::rollBack();
                return $this->errorResponse('Discount not found', 404);
            }

            $discount->delete();

            DB::commit();

            return $this->successResponse(null, 'Discount deleted successfully');

        } catch (\Throwable $e) {

            DB::rollBack();

            return $this->errorResponse('Failed to delete discount', 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PaymentTerm;
use App\Services\PaymentTermService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\ApiResponseTrait;
class PaymentTermController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search'=>$request->query('search'),
            ];

            $paymentTerms = PaymentTermService::getAll( $user->company_id, $options);

            return $this->successResponse($paymentTerms, 'Payment terms fetched successfully', Response::HTTP_OK);
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
                'title' => ['required', 'string', 'max:255'],
                'code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('payment_terms', 'code')->where(function ($query) use ($user) {
                        return $query->where('company_id', $user->company_id)
                            ->whereNull('deleted_at');
                    }),
                ],
                'active' => ['boolean'],
            ]);

            $paymentTerm = PaymentTerm::create([
                'company_id' => $user->company_id,
                'title' => $validated['title'],
                'code' => $validated['code'],
                'active' => $validated['active'] ?? true,
            ]);

            DB::commit();
            return $this->successResponse($paymentTerm, 'Payment term created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $paymentTerm = PaymentTerm::where('company_id', $user->company_id)->findOrFail($id);

            $validated = $request->validate([
                'title' => ['sometimes', 'string', 'max:255'],
                'code' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('payment_terms', 'code')
                        ->ignore($paymentTerm->id)
                        ->where(function ($query) use ($user) {
                            return $query->where('company_id', $user->company_id)
                                ->whereNull('deleted_at');
                        }),
                ],
                'active' => ['sometimes', 'boolean'],
            ]);

            $paymentTerm->update($validated);

            DB::commit();
            return $this->successResponse($paymentTerm, 'Payment term updated successfully', Response::HTTP_OK);
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
            $paymentTerm = PaymentTerm::where('company_id', $user->company_id)->findOrFail($id);

            $paymentTerm->delete();

            DB::commit();
            return $this->successResponse([], 'Payment term deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

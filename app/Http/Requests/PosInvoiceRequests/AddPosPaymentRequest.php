<?php
namespace App\Http\Requests\PosInvoiceRequests;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class AddPosPaymentRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [

            'pos_invoice_id' => [
                'required',
                Rule::exists('pos_invoices', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'pos_cash_tray_id' => [
                'required',
                Rule::exists('pos_cash_trays', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'pos_session_id' => [
                'required',
                Rule::exists('pos_sessions', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'payments' => ['required', 'array', 'min:1'],

            'payments.*.pos_session_id' => [
                'required',
                Rule::exists('pos_sessions', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'payments.*.pos_cash_tray_id' => [
                'required',
                Rule::exists('pos_cash_trays', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'payments.*.cashing_method_id' => [
                'required',
                Rule::exists('cashing_methods', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'payments.*.currency_id' => [
                'required',
                function ($attribute, $value, $fail) use ($companyId) {

                    $companyCurrencies = auth()->user()
                        ->company
                        ->currencies
                        ->pluck('id')
                        ->toArray();

                    if (!in_array($value, $companyCurrencies)) {
                        $fail("The selected currency is invalid for your company.");
                    }
                },
            ],

            'payments.*.amount' => [
                'required',
                'numeric',
                'min:0.0001'
            ],

            'payments.*.exchange_rate' => [
                'required',
                'numeric',
                'min:0.000001'
            ],
            'client_id'=>[
                Rule::exists('clients', 'id')
                ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'change_total'=>['nullable','numeric'],
            'remaining_total'=>['nullable','numeric']
        ];
    }
}

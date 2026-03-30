<?php

namespace App\Http\Requests\SalesOrderRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesOrderRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
//            'sales_order_number' => [
//                'required', 'string', 'max:50',
//                Rule::unique('sales_orders', 'sales_order_number')
//                    ->where(fn($query) => $query->where('company_id', $companyId))
//            ],
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(fn($query) => $query->where('company_id', $companyId))
            ],
            'currency_id' => [
                'required',
                function ($attribute, $value, $fail) use ($companyId) {
                    $companyCurrencies = auth()->user()->company->currencies->pluck('id')->toArray();
                    if ($value && !in_array($value, $companyCurrencies)) {
                        $fail("The selected currency is invalid for your company.");
                    }
                },

            ],
            'price_list_id' => [
                'nullable',
                'integer',
                Rule::exists('price_lists', 'id')->where(fn($query) => $query->where('company_id', $companyId))
            ],
            'salesperson_id' => ['nullable',   Rule::exists('users', 'id')->where(fn($query) => $query->where('company_id', $companyId))],
            'payment_term_id' => ['nullable',  Rule::exists('payment_terms', 'id')->where(fn($query) => $query->where('company_id', $companyId))],
            'commission_method_id' => ['nullable',   Rule::exists('commission_methods', 'id')->where(fn($query) => $query->where('company_id', $companyId))],
            'cashing_method_id' => ['nullable',   Rule::exists('cashing_methods', 'id')->where(fn($query) => $query->where('company_id', $companyId))],

            'status' => ['nullable', Rule::in(['draft', 'confirmed', 'cancelled'])],
            'input_date' => ['nullable', 'date'],
            'validity' => ['nullable', 'date'],

            'special_discount' => ['nullable', 'numeric', 'min:0'],
            'special_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'global_discount' => ['nullable', 'numeric', 'min:0'],
            'global_discount_amount' => ['nullable', 'numeric', 'min:0'],

            'vat' => ['nullable', 'numeric', 'min:0'],
            'vat_lebanese' => ['nullable', 'numeric', 'min:0'],
            'vat_exempt' => ['boolean'],
            'vat_inclusive_prices' => ['boolean'],
            'before_vat_prices' => ['boolean'],

            'total_before_vat' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0'],
            'commission_total' => ['nullable', 'numeric', 'min:0'],

            'not_printed' => ['boolean'],
            'printed_as_vat_exempt' => ['boolean'],
            'printed_as_percentage' => ['boolean'],

            'terms_and_conditions' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
            // Items array
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.type'=>['required',Rule::exists('line_types','id')],
            'lines.*.item_id' => [
                'nullable', 'integer',
                Rule::exists('items', 'id')->where(fn($query) => $query->where('company_id', $companyId))
            ],
            'lines.*.combo_id' => [
                'nullable', 'integer',
                Rule::exists('combos', 'id')->where(fn($query) => $query->where('company_id', $companyId))
            ],
            'lines.*.qty' => ['required', 'numeric', 'min:0.001'],
            'lines.*.description' => ['nullable', 'string', 'min:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.total' => ['required', 'numeric', 'min:0'],
//            'lines.*.package_id' => [
//                'nullable', 'integer',
//                Rule::exists('packages', 'id')
//            ],
            'lines.*.warehouse_id' => [
                'nullable', 'integer',
                Rule::exists('warehouses', 'id')->where(fn($query) => $query->where('company_id', $companyId))
            ],
            'lines.*.note' => ['nullable', 'string'],
        ];
    }
}

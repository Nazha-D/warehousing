<?php
namespace App\Http\Requests\SalesInvoiceRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesInvoiceRequest extends ApiRequest
{
    public function rules()
    {
        $companyId = auth()->user()->company_id;

        return [
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where(function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                }),
            ],
            'value_date' => ['required', 'date'],
            'payment_term_id' => ['nullable', 'exists:payment_terms,id'],
            'currency_id' => [
                'required',
                function ($attribute, $value, $fail) use ($companyId) {
                    $companyCurrencies = auth()->user()->company->currencies->pluck('id')->toArray();
                    if ($value && !in_array($value, $companyCurrencies)) {
                        $fail("The selected currency is invalid for your company.");
                    }
                },
            ],
//            'sales_order_id' => ['nullable', 'exists:sales_orders,id'],
            'price_list_id' => ['nullable', 'exists:price_lists,id'],
            'salesperson_id' => ['nullable', 'exists:users,id'],
            'commission_method_id' => ['nullable', 'exists:commission_methods,id'],
            'cashing_method_id' => ['nullable', 'exists:cashing_methods,id'],
            'sales_invoice_number' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'commission_rate' => ['nullable', 'numeric'],
            'commission_total' => ['nullable', 'numeric'],
            'special_discount' => ['nullable', 'numeric'],
            'special_discount_amount' => ['nullable', 'numeric'],
            'global_discount' => ['nullable', 'numeric'],
            'global_discount_amount' => ['nullable', 'numeric'],
            'vat_lebanese' => ['nullable', 'numeric'],
            'vat' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'total_before_vat' => ['nullable', 'numeric'],
            'vat_exempt' => ['nullable', 'boolean'],
            'not_printed' => ['nullable', 'boolean'],
            'printed_as_vat_exempt' => ['nullable', 'boolean'],
            'printed_as_percentage' => ['nullable', 'boolean'],
            'vat_inclusive_prices' => ['nullable', 'boolean'],
            'before_vat_prices' => ['nullable', 'boolean'],
            'code' => ['nullable', 'string'],
            'title' => ['nullable', 'string'],
            'delivered_from_warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'invoice_delivery_date' => ['nullable', 'date'],
            'input_date' => ['nullable', 'date'],
            'company_header_id' => ['nullable', 'exists:company_headers,id'],
            'invoice_type' => ['nullable', 'string'],
            'car_id' => ['nullable', 'exists:cars,id'],
            'terms_and_condition_id' => ['nullable', 'exists:terms_and_conditions,id'],

            'delivery_lines' => ['required', 'array', 'min:1'],
            'delivery_lines.*.delivery_line_id' => [
                'required',
                'integer',
                'exists:delivery_lines,id',
            ],
            'delivery_lines.*.quantity' => [
                'required',
                'numeric',
                'min:0.0001'
            ],
        ];
    }
}

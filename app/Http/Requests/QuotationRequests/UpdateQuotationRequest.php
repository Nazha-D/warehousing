<?php

namespace App\Http\Requests\QuotationRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuotationRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {

        return [
            'client_id' => [
                'sometimes',
                Rule::exists('clients', 'id')->where(function ($query) {
                    $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'price_list_id' => [
                'nullable',
                Rule::exists('price_lists', 'id')->where(function ($query) {
                    $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'company_header_id' => [
                'nullable',
                Rule::exists('company_headers', 'id')->where(function ($query) {
                    $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'payment_term_id' => [
                'nullable',
                Rule::exists('payment_terms', 'id')
            ],
            'terms_and_conditions_id' => [
                'nullable',
                Rule::exists('terms_and_conditions', 'id')->where(function ($query) {
                    $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'delivery_term_id' => [
                'nullable',
                Rule::exists('delivery_terms', 'id')->where(function ($query) {
                    $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'commission_method_id' => [
                'nullable',
                Rule::exists('commission_methods', 'id'),
            ],
            'currency_id' => [
                'sometimes',
                function ($attribute, $value, $fail) {
                    $companyCurrencies = auth()->user()->company->currencies->pluck('id')->toArray();
                    if ($value && !in_array($value, $companyCurrencies)) {
                        $fail("The selected currency is invalid for your company.");
                    }
                },
            ],
            'cashing_method_id' => [
                'nullable',
                Rule::exists('cashing_methods', 'id'),
            ],
            'salesperson_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'order_lines'=>['array'],
            'input_date'=>['sometimes','date'],
            // Boolean fields
            'vat_exempt' => ['sometimes', 'boolean'],
            'not_printed' => ['sometimes', 'boolean'],
            'printed_as_vat_exempt' => ['sometimes', 'boolean'],
            'printed_as_percentage' => ['sometimes', 'boolean'],
            'vat_inclusive_prices' => ['sometimes', 'boolean'],
            'before_vat_prices' => ['sometimes', 'boolean'],
            'reference' => ['nullable', 'string'],
            'cancellation_reason' => ['nullable', 'string'],
            'title' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'chance' => ['nullable', 'string'],
            'validity' => ['nullable', 'date'],
            'special_discount'=>['nullable','numeric'],
            'special_discount_amount'=>['nullable','numeric'],
            'global_discount'=>['nullable','numeric'],
            'global_discount_amount'=>['nullable','numeric'],
            'vat'=>['nullable','numeric'],
            'vat_lebanese'=>['nullable','numeric'],
            'total_before_vat'=>['nullable','numeric'],
            'total'=>['nullable','numeric'],

        ];
    }
}

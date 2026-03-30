<?php

namespace App\Http\Requests\QuotationRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuotationRequest extends ApiRequest
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
        $companyId=auth()->user()->company_id;
        return [
            'client_id' => [
                'required',
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
                'required',
                function ($attribute, $value, $fail) use ($companyId) {
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
            'input_date'=>['required','date'],
            // Boolean fields
            'vat_exempt' => ['required', 'boolean'],
            'not_printed' => ['required', 'boolean'],
            'printed_as_vat_exempt' => ['required', 'boolean'],
            'printed_as_percentage' => ['required', 'boolean'],
            'vat_inclusive_prices' => ['required', 'boolean'],
            'before_vat_prices' => ['required', 'boolean'],
               'reference' => ['nullable', 'string'],
            'chance' => ['nullable', 'string'],
            'cancellation_reason' => ['nullable', 'string'],
            'title' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'validity' => ['nullable', 'date'],
            'special_discount'=>['nullable','numeric'],
        'special_discount_amount'=>['nullable','numeric'],
        'global_discount'=>['nullable','numeric'],
        'global_discount_amount'=>['nullable','numeric'],
        'vat'=>['nullable','numeric'],
        'vat_lebanese'=>['nullable','numeric'],
        'total_before_vat'=>['nullable','numeric'],
        'total'=>['nullable','numeric'],
            'commission_rate'=>['nullable','numeric'],
            'commission_total'=>['nullable','numeric'],

            //     'code'=>['required',Rule::unique('quotations', 'code')->where(function ($query) {
        //         $query->where('company_id', auth()->user()->company_id);
        //     })->whereNull('deleted_at')]
         ];
    }

    /**
     * Get the validation rules that apply after the initial rules.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $vatExempt = $this->input('vatExempt');

            if ($vatExempt) {
                $fields = [
                    $this->input('not_printed'),
                    $this->input('printed_as_vat_exempt'),
                    $this->input('printed_as_percentage'),
                ];

                if (in_array(1,$fields) !== true) {
                    $validator->errors()->add(
                        'vat_exempt_group',
                        'If VAT Exempt is true, one and only one of "not_printed", "printed_as_vat_exempt", or "printed_as_percentage" must be true.'
                    );
                }
            } else {
                $fields = [
                    $this->input('vat_inclusive_prices'),
                    $this->input('before_vat_prices'),
                ];

                if (in_array(1,$fields) !== true) {
                    $validator->errors()->add(
                        'vat_non_exempt_group',
                        'If VAT Exempt is false, one and only one of "vat_inclusive_prices" or "before_vat_prices" must be true.'
                    );
                }
            }
        });
    }
}

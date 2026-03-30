<?php

namespace App\Http\Requests\PriceListRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StorePriceListRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'name' => ['required', 'string', 'max:255',Rule::unique('price_lists','name')->where(fn($query) => $query->where('company_id', $companyId))],

            'currency_id' => [
                'required',
                function ($attribute, $value, $fail) use ($companyId) {
                    $companyCurrencies = auth()->user()
                        ->company
                        ->currencies
                        ->pluck('id')
                        ->toArray();

                    if (!in_array($value, $companyCurrencies)) {
                        $fail('The selected currency is invalid for your company.');
                    }
                },
            ],

            'parent_id' => [
                'nullable',
                Rule::exists('price_lists', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'active' => ['sometimes', 'boolean'],

            'rules' => ['sometimes', 'array'],

            // ===== Rules validation =====

            'rules.*.apply_on' => ['required', 'string', 'in:item,category,global'],
            'rules.*.base_source' => ['required', 'string', 'in:base_price,parent_list'],

            'rules.*.item_id' => [
                'nullable',
                'integer',
                'required_if:rules.*.apply_on,item',
            ],

            'rules.*.category_id' => [
                'nullable',
                'integer',
                'required_if:rules.*.apply_on,category',
            ],

            'rules.*.computation_method' => [
                'required',
                'string',
                'in:percentage,fixed_amount,fixed_price',
            ],

            'rules.*.value' => ['required', 'numeric'],

            'rules.*.priority' => ['sometimes', 'integer', 'min:0'],

            'rules.*.start_date' => ['nullable', 'date'],
            'rules.*.end_date' => ['nullable', 'date', 'after:rules.*.start_date'],

            'items' => ['sometimes', 'array'],

            'items.*.item_id' => [
                'required',
                'integer',
                Rule::exists('items', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'items.*.manual_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')
                    ->where(fn ($q) => $q->where('company_id', auth()->user()->company_id))
            ],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('rules') && $this->filled('items')) {
                $validator->errors()->add(
                    'items',
                    'You cannot define both rules and items in the same price list.'
                );
            }
        });
    }

}

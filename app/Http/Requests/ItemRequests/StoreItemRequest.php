<?php

namespace App\Http\Requests\ItemRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreItemRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'item_type_id' => [
                'nullable',
                Rule::exists('item_types', 'id'),
            ],

            'main_code' => [
                'nullable',
                Rule::unique('items', 'main_code')
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    ),
            ],

            'item_name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'main_description' => ['nullable', 'string', 'max:255'],
            'second_language_description' => ['nullable', 'string', 'max:255'],

            'taxation_group_id' => [
                'nullable',
                Rule::exists('taxation_groups', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'supplier_codes.*' => [
                'nullable',
                Rule::unique('supplier_codes', 'code')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'alternative_codes.*' => [
                'nullable',
                Rule::unique('alternative_codes', 'code')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'barcodes.*' => [
                'nullable',
                Rule::unique('barcodes', 'code')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'item_groups.*' => [
                'nullable',
                Rule::exists('item_groups', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
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

            'pos_currency_id' => [
                'required',
                function ($attribute, $value, $fail) use ($companyId) {
                    $companyCurrencies = auth()->user()->company->currencies->pluck('id')->toArray();
                    if ($value && !in_array($value, $companyCurrencies)) {
                        $fail("The selected POS currency is invalid for your company.");
                    }
                },
            ],

            'price_currency_id' => [
                'required',
                function ($attribute, $value, $fail) use ($companyId) {
                    $companyCurrencies = auth()->user()->company->currencies->pluck('id')->toArray();
                    if ($value && !in_array($value, $companyCurrencies)) {
                        $fail("The selected price currency is invalid for your company.");
                    }
                },
            ],

            'package_id' => [
                'nullable',
                Rule::exists('packages', 'id'),
            ],
           'subref_id' => [
                'nullable',
                Rule::exists('subrefs', 'id'),
            ],

            'default_transaction_package_id' => [
                'nullable',
                'lte:package_id',
            ],

       //     'weight' => ['nullable', 'numeric'],
         //   'volume' => ['nullable', 'numeric'],
            'unit_cost' => ['nullable', 'numeric'],
            'unit_price' => ['nullable', 'numeric'],
            'line_discount_limit' => ['nullable', 'numeric'],
            'blocked' => ['nullable', 'boolean'],
            'discontinued' => ['nullable', 'boolean'],
            'can_be_sold' => ['nullable', 'boolean'],
            'can_be_purchased' => ['nullable', 'boolean'],
            'warranty' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
            'show_on_pos' => ['nullable', 'boolean'],

            'last_allowed_purchase_date' => ['nullable', 'date'],
            'package_unit_name'=>['nullable', 'string'],
            'package_unit_quantity'=>['nullable', 'numeric'],
            'package_set_name'=>['nullable', 'string'],
            'package_set_quantity'=>['nullable', 'numeric'],
            'package_superset_name'=>['nullable', 'string'],
            'package_superset_quantity'=>['nullable', 'numeric'],
            'package_palette_name'=>['nullable', 'string'],
            'package_palette_quantity'=>['nullable', 'numeric'],
            'package_container_name'=>['nullable', 'string'],
            'package_container_quantity'=>['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_codes.*.unique' =>
                'The supplier code :input has already been taken.',

            'alternative_codes.*.unique' =>
                'The alternative code :input has already been taken.',

            'barcodes.*.unique' =>
                'The barcode :input has already been taken.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $arraysToCheck = ['supplier_codes', 'alternative_codes', 'barcodes'];

            foreach ($arraysToCheck as $field) {
                $values = collect($this->input($field, []));
                $duplicates = $values->duplicates();

                foreach ($duplicates as $duplicate) {
                    $validator->errors()
                        ->add($field, "Duplicate {$field} '{$duplicate}' is sent.");
                }
            }
        });
    }


}

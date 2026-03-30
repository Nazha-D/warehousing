<?php

namespace App\Http\Requests\ReplenishmentRequests;

use App\Http\Requests\ApiRequest;

use Illuminate\Validation\Rule;

class StoreReplenishmentWithItemsRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            /* ================= Document ================= */

            'warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId),
            ],

            'currency_id' => [
                'required',
                Rule::exists('company_currencies', 'currency_id')
                    ->where('company_id', $companyId),
            ],

            'date' => ['required', 'date'],

            'manual_reference' => [
                'nullable',
                'string',
                'max:100'
            ],

            /* ================= Items ================= */

            'items' => [
                'required',
                'array',
                'min:1'
            ],

            'items.*.item_id' => [
                'required',
                'distinct',
                Rule::exists('items', 'id')
                    ->where('company_id', $companyId),
            ],

            'items.*.package_id' => [
                'nullable',
                'numeric',
                Rule::exists('packages','id')
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'items.*.unit_cost' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'items.*.notes' => [
                'nullable',

            ],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required.',
            'items.*.item_id.distinct' => 'Duplicate items are not allowed.',
        ];
    }
}

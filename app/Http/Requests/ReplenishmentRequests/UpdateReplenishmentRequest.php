<?php

namespace App\Http\Requests\ReplenishmentRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateReplenishmentRequest extends ApiRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $companyId = $this->user()->company_id;

        return [
            /* ================= Document ================= */

            'warehouse_id' => [
                'sometimes',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId),
            ],

            'currency_id' => [
                'sometimes',
                Rule::exists('company_currencies', 'currency_id')
                    ->where('company_id', $companyId),
            ],

            'date' => ['sometimes', 'date'],

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
}

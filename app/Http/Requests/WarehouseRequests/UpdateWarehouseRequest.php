<?php

namespace App\Http\Requests\WarehouseRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId   = auth()->user()->company_id;
        $warehouseId = $this->route('id');

        return [

            /* =======================
             *  MAIN DATA
             * ======================= */

            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name')
                    ->ignore($warehouseId)
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    ),
            ],

            'type' => ['sometimes', 'nullable', 'string', 'max:50'],

            'address' => ['sometimes', 'nullable', 'string', 'max:500'],


            'blocked' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\WarehouseRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [

            /* =======================
             *  MAIN DATA
             * ======================= */

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name')
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    ),
            ],


            'type' => ['nullable', 'string', 'max:50'],

            'address' => ['nullable', 'string', 'max:500'],

            'blocked' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}

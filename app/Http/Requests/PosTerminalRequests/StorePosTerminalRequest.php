<?php

namespace App\Http\Requests\PosTerminalRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePosTerminalRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;
        return [

            'warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
                 Rule::unique('pos_terminals', 'name')
                     ->where(fn ($q) =>
                     $q->where('company_id', $companyId)
                         ->whereNull('deleted_at')
                     ),
            ],

            'address' => [
                'nullable',
                'string',
                'max:500'
            ],

//            'pos_number' => [
//                'required',
//                'string',
//                'max:100',
//                Rule::unique('pos_terminals')
//                    ->where(fn ($query) =>
//                    $query->where('company_id', $this->company_id)
//                    )
//            ],

            'is_active' => [
                'boolean'
            ],
        ];
    }
}

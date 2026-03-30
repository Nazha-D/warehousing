<?php

namespace App\Http\Requests\PosTerminalRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePosTerminalRequest extends ApiRequest
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
                'sometimes',
                Rule::exists('warehouses', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),

            ],

            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('pos_terminals', 'name')
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    )->ignore($this->posTerminal),
            ],

            'address' => [
                'nullable',
                'string',
                'max:500'
            ],


            'is_active' => [
                'boolean'
            ],
        ];
    }
}

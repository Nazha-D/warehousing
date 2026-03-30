<?php

namespace App\Http\Requests\ItemGroupRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemGroupRequest extends FormRequest
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
        $companyId = auth()->user()->company_id;

        return [
            'name'=>['sometimes','string', 'max:255'],
            'code'=>['sometimes', Rule::unique('item_groups', 'code')
                ->where(fn($q) => $q->where('company_id', $companyId))
               ->ignore($this->itemGroup->id)
                ->whereNull('deleted_at')]
        ];
    }
}

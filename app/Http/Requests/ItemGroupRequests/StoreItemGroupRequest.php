<?php

namespace App\Http\Requests\ItemGroupRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreItemGroupRequest extends ApiRequest
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
            'groups' => ['required', 'array'],
            'groups.*.name' => ['required', 'string', 'max:255'],
            'groups.*.code' => [
                'required',
                'string',
                Rule::unique('item_groups', 'code')
                    ->where(fn($q) => $q->where('company_id', $companyId))
                    ->whereNull('deleted_at'),
            ],

            // For recursive children
            'groups.*.children' => ['nullable', 'array'],
            'groups.*.children.*.name' => ['required_with:groups.*.children', 'string', 'max:255'],
            'groups.*.children.*.code' => [
                'required_with:groups.*.children',
                'string',
                Rule::unique('item_groups', 'code')
                    ->where(fn($q) => $q->where('company_id', $companyId))
                    ->whereNull('deleted_at'),
            ],
            'groups.*.children.*.children' => ['nullable', 'array'],

            // 3rd level (and can easily extend deeper if needed)
            'groups.*.children.*.children.*.name' => ['required_with:groups.*.children.*.children', 'string', 'max:255'],
            'groups.*.children.*.children.*.code' => [
                'required_with:groups.*.children.*.children',
                'string',
                Rule::unique('item_groups', 'code')
                    ->where(fn($q) => $q->where('company_id', $companyId))
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    public function messages()
    {
        return [
            'groups.required' => 'At least one group must be provided.',
            '*.name.required' => 'The name field is required for all groups and children.',
            '*.code.required' => 'The code field is required for all groups and children.',
            '*.code.unique' => 'Item Group Code :input has already been taken.',
        ];
    }
}

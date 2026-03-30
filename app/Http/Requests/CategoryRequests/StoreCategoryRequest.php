<?php

namespace App\Http\Requests\CategoryRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'categories' => ['required', 'array'],
            'categories.*.name' => ['required', 'string', 'max:255'],


            // For recursive children
            'categories.*.children' => ['nullable', 'array'],
            'categories.*.children.*.name' => ['required_with:categories.*.children', 'string', 'max:255'],

            'categories.*.children.*.children' => ['nullable', 'array'],

            // 3rd level (and can easily extend deeper if needed)
            'categories.*.children.*.children.*.name' => ['required_with:categories.*.children.*.children', 'string', 'max:255'],

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
            'categories.required' => 'At least one group must be provided.',
            '*.name.required' => 'The name field is required for all groups and children.',

        ];
    }
}

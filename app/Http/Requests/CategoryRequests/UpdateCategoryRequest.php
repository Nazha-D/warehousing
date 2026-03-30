<?php

namespace App\Http\Requests\CategoryRequests;

use App\Http\Requests\ApiRequest;

use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends ApiRequest
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

        ];
    }
}

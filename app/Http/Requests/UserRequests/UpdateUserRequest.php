<?php

namespace App\Http\Requests\UserRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends ApiRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules['name'] = ['sometimes','string'];
        $rules['email'] = [
            'sometimes',
            'email',
            'max:255',
            Rule::unique('users', 'email')->where(function ($query) {
                $query->where('company_id', auth()->user()->company_id);
            })->ignore($this->user),
        ];
        $rules['password'] = [
            'sometimes',
            'min:3',
            'confirmed',
        ];
        $rules['is_salesperson'] = ['sometimes','boolean'];
        $rules['is_active'] = ['sometimes','boolean'];

        $rules['roles'] = [
            'sometimes',
            'array',        // نتحقق من أنها مصفوفة
            'distinct',     // نتحقق من عدم وجود duplicate IDs
        ];

        $rules['roles.*'] = [
            'integer',      // كل عنصر يجب أن يكون رقم
            Rule::exists('roles', 'id')->where(function ($query) {
                $query->where('company_id', auth()->user()->company_id);
            }),
        ];

        if ($this->is_salesperson) {
            $rules['cashing_method_id'] = [
                //    'required',
                Rule::exists('cashing_methods', 'id'),
            ];
            $rules['commission_method_id'] = [
                //    'required',
                Rule::exists('commission_methods', 'id'),
            ];
            $rules['commission'] = [
                //    'required',
                'decimal:0,2',
                'between:0,100',
            ];
        }

        return $rules;
    }
}

<?php

namespace App\Http\Requests\AuthRequests;

use App\Http\Requests\ApiRequest;

use Illuminate\Validation\Rule;

class RegisterRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId=$this->companyId;
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'companyId'=>'nullable|exists:companies,id',
            'roles'=>'array|required',
            'roles.*'=>['exists:roles,id',  Rule::exists('roles', 'id')->where(function ($query)use($companyId) {
                $query->where('company_id', $companyId ?? auth()->user()->company_id );
            })]
        ];
    }
}

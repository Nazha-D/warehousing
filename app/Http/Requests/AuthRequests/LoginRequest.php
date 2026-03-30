<?php

namespace App\Http\Requests\AuthRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId=$this->companyId;
        return [
            'email' => 'required|email|exists:users,email',

            'password' => 'required|string',
        ];
    }
}

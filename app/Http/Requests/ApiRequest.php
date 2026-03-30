<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $message = $validator->errors()->count() > 1
            ? $validator->errors()->first() . ' (And Others)'
            : $validator->errors()->first();

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $message,
                'data'    => null,
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}

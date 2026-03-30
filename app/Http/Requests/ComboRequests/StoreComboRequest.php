<?php

namespace App\Http\Requests\ComboRequests;

use App\Http\Requests\ApiRequest;

use Illuminate\Validation\Rule;
class StoreComboRequest extends ApiRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:combos,name'],
           'code'=>['nullable', Rule::unique('combos')->where(function ($query) {
                return $query->where('company_id', auth()->user()->company_id);
            }),

            ]
        ];
    }
}

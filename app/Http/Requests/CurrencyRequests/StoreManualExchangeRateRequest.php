<?php

namespace App\Http\Requests\CurrencyRequests;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StoreManualExchangeRateRequest extends ApiRequest
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
            'from_currency_id' => [
                'required',
                'integer',
                Rule::exists('company_currencies', 'currency_id')
                    ->where('company_id', $companyId),
            ],
            'to_currency_id' => [
                'required',
                'integer',
                Rule::exists('company_currencies', 'currency_id')
                    ->where('company_id', $companyId),
            ],
            'rate' => 'required|numeric|min:0',
        ];
    }
}

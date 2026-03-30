<?php

namespace App\Http\Requests\CashTrayRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class CloseCashTrayRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'counted_balances' => ['required', 'array'],
            'counted_balances.*.currency_id' => ['required', 'exists:currencies,id'],
            'counted_balances.*.amount' => ['required', 'numeric'],
        ];
    }
}

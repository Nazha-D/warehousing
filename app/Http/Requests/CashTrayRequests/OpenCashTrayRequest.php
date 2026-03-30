<?php

namespace App\Http\Requests\CashTrayRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenCashTrayRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // أو تحقق صلاحيات الـ user إذا لازم
    }

    public function rules(): array
    {
        $companyId=auth()->user()->company_id;
        return [
           'pos_terminal_id' => ['required',
               Rule::exists('pos_terminals','id')->where(fn ($q) => $q->where('company_id', $companyId))],
           // 'type' => ['required', 'string', 'in:cash,card,bank,mobile_money'],
            'opening_balances' => ['required', 'array'],
            'opening_balances.*.currency_id' => ['required', 'exists:currencies,id'],
            'opening_balances.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}

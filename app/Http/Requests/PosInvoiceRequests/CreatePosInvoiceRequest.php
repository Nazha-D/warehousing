<?php

namespace App\Http\Requests\PosInvoiceRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePosInvoiceRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId=auth()->user()->company_id;
        return [
            'session_id' => ['required',Rule::exists('pos_sessions', 'id')
                ->where(fn ($q) => $q->where('company_id', $companyId)),],
            'client_id' => ['nullable',Rule::exists('clients', 'id')
                ->where(fn ($q) => $q->where('company_id', $companyId)),],
            'discount_id' => ['nullable',Rule::exists('discounts', 'id')
                ->where(fn ($q) => $q->where('company_id', $companyId)),],
            'car_id' => ['nullable','exists:cars,id'],
            'currency_id' => ['required',  function ($attribute, $value, $fail) use ($companyId) {
                $companyCurrencies = auth()->user()->company->currencies->pluck('id')->toArray();
                if ($value && !in_array($value, $companyCurrencies)) {
                    $fail("The selected currency is invalid for your company.");
                }
            },],
            'pos_terminal_id' => ['required',Rule::exists('pos_terminals', 'id')
                ->where(fn ($q) => $q->where('company_id', $companyId)),],

            'pos_cash_tray_id' => ['required',Rule::exists('pos_cash_trays', 'id')
                ->where(fn ($q) => $q->where('company_id', $companyId)),],

            'exchange_rate' => ['required','numeric'],
            'subtotal' => ['required','numeric'],
            'tax_total' => ['required','numeric'],
            'discount_total' => ['nullable','numeric'],
            'custom_discount_total' => ['nullable','numeric'],

            'grand_total' => ['required','numeric'],
            'remaining_total' => ['required','numeric'],
            'change_total' => ['numeric'],

            'note' => ['nullable','string'],

            'lines' => ['required','array','min:1'],

            'lines.*.item_id' => ['required',Rule::exists('items', 'id')
                ->where(fn ($q) => $q->where('company_id', $companyId)),],
            'lines.*.quantity' => ['required','numeric','min:0.0001'],
            'lines.*.unit_price' => ['required','numeric','min:0'],
            'lines.*.discount_value' => ['nullable','numeric','min:0'],
            'lines.*.custom_discount_value' => ['nullable','numeric','min:0'],

            'lines.*.discount_id' => ['nullable','numeric',Rule::exists('discounts', 'id')
                ->where(fn ($q) => $q->where('company_id', $companyId)),],

            'lines.*.tax_value' => ['nullable','numeric','min:0'],
        ];
    }
}

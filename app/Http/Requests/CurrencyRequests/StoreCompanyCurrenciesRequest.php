<?php

namespace App\Http\Requests\CurrencyRequests;
use App\Http\Requests\ApiRequest;

class StoreCompanyCurrenciesRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

              return [
                  'currency_ids'   => 'required|array|min:1',
                  'currency_ids.*' => 'exists:currencies,id',
              ];

    }
}

<?php

namespace App\Http\Requests\PriceListRequests;

use App\Http\Requests\ApiRequest;

class UpdatePriceListRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'currency_id' => 'sometimes|exists:currencies,id',
            'parent_list_id' => 'nullable|exists:price_lists,id',
            'active' => 'sometimes|boolean',

            'rules' => 'sometimes|array',

            'rules.*.id' => 'sometimes|exists:price_rules,id',

            'rules.*.type' => 'required|string|in:item,category,brand',
            'rules.*.operator' => 'required|string|in:=,>,<,>=,<=',
            'rules.*.value' => 'required',

            'rules.*.adjustment_type' => 'required|string|in:fixed,percentage',
            'rules.*.adjustment_value' => 'required|numeric',
        ];
    }
}

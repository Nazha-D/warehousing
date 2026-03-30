<?php

namespace App\Http\Requests\TransferRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;


class ReceiveTransferRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {  $companyId=auth()->user()->company_id;
        return [
            'received_items' => ['required', 'array', 'min:1'],
            'received_items.*.transfer_item_id' => ['required', 'exists:transfer_items,item_id',   Rule::exists('items', 'id')
                ->where('company_id', $companyId)],
            'received_items.*.received_qty' => ['required', 'numeric', 'min:0.0001'],
            'received_items.*.package_id' => ['nullable',   Rule::exists('packages', 'id')],
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('received_items')) {
            $this->merge([
                'received_items' => collect($this->received_items)->map(function ($item) {
                    $item['package_id'] = isset($item['package_id']) ? trim($item['package_id']) : null;
                    return $item;
                })->toArray(),
            ]);
        }
    }
}

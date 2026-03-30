<?php

namespace App\Http\Requests\TransferRequests;

use App\Http\Requests\ApiRequest;

use Illuminate\Validation\Rule;

class StoreTransferRequest extends ApiRequest
{
    public function authorize(): bool
    {
        // يمكن إضافة check على صلاحيات اليوزر لاحقًا
        return true;
    }

    public function rules(): array
    {
        $companyId=auth()->user()->company_id;
        return [
            'date' => ['required', 'date'],
            'manual_reference' => ['nullable', 'string', 'max:255'],
            'src_warehouse_id' => [
                'required',
                  Rule::exists('warehouses', 'id')
                ->where('company_id', $companyId),],
            'dest_warehouse_id' => [
                'required',
               'different:src_warehouse_id',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId),],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => [
                'required',
                Rule::exists('items', 'id')
                    ->where('company_id', $companyId)],
            'items.*.qty_to_transfer' => ['required', 'numeric', 'min:0.0001'],
            'items.*.package_id' => ['nullable',   Rule::exists('packages', 'id')],
            'items.*.note' => ['nullable', 'string'],
        ];
    }

    public function prepareForValidation()
    {
        // إزالة الفراغات من package_name و manual_reference
        if ($this->has('items')) {
            $this->merge([
                'items' => collect($this->items)->map(function ($item) {
                    $item['package_name'] = isset($item['package_name']) ? trim($item['package_name']) : null;
                    return $item;
                })->toArray(),
            ]);
        }

        if ($this->has('manual_reference')) {
            $this->merge([
                'manual_reference' => trim($this->manual_reference)
            ]);
        }
    }
}

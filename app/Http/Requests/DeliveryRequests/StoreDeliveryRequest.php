<?php

namespace App\Http\Requests\DeliveryRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends ApiRequest
{
    public function authorize(): bool
    {
           return true; // حسب الحاجة يمكن تقييدها حسب الصلاحيات
    }

    public function rules(): array
    {
        return [
            'client_id'          => ['required', 'integer', 'exists:clients,id'],
            'driver_id'          => ['nullable', 'integer', 'exists:users,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'expected_delivery'  => ['nullable', 'date'],
            'date'               => ['nullable', 'date'],
            'lines'          => ['required', 'array', 'min:1'],
            'lines.*.sales_order_line_id'        => ['required','integer', 'exists:sales_order_lines,id'],
            'lines.*.qty'        => ['required','integer'],


        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'You must select at least one sales order line.',
        ];
    }

    /**
     * تجهيز البيانات بعد الفحص
     */
    public function validatedLines(): array
    {
        return $this->validated()['lines'] ?? [];
    }
}

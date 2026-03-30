<?php

namespace App\Http\Requests\ClientRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;
        $clientId  = $this->route('id'); // مهم: Route Model Binding

        return [

            /* =======================
             * CLIENT MAIN DATA
             * ======================= */

            'type' => ['sometimes', 'string'],
            'name' => ['sometimes', 'string', 'max:255'],

            'client_number' => [
                'sometimes',
                'nullable',
                'string',
                Rule::unique('clients', 'client_number')
                    ->ignore($clientId)
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    ),
            ],

            'client_company_id' => [
                'sometimes',
                'nullable',
                Rule::exists('clients', 'id')
                    ->where('company_id', $companyId),
            ],

            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'zip' => ['sometimes', 'nullable', 'string', 'max:50'],
            'street' => ['sometimes', 'nullable', 'string', 'max:255'],
            'floor_and_building' => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_position' => ['sometimes', 'nullable', 'string', 'max:255'],

            'phone_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'phone_number' => [
                'sometimes', 'nullable', 'string', 'max:50',
                Rule::unique('clients', 'phone_number')
                    ->ignore($clientId)
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    ),
            ],

            'mobile_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'mobile_number' => [
                'sometimes', 'nullable', 'string', 'max:50',
                Rule::unique('clients', 'mobile_number')
                    ->ignore($clientId)
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    ),
            ],

            'reference' => ['sometimes', 'nullable', 'string', 'max:255'],

            'email' => [
                'sometimes', 'nullable', 'email', 'max:255',
                Rule::unique('clients', 'email')
                    ->ignore($clientId)
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    ),
            ],

            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'string'],

            'tax_id' => [
                'sometimes', 'nullable', 'string', 'max:255',
                Rule::unique('clients', 'tax_id')
                    ->ignore($clientId)
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    ),
            ],

            'website' => ['sometimes', 'nullable', 'string', 'max:255'],

            /* =======================
             * CONTACT DATA
             * ======================= */

            'contact_type' => ['sometimes', 'nullable', 'string'],
            'contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_zip' => ['sometimes', 'nullable', 'string', 'max:50'],
            'contact_street' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_phone_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'contact_phone_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'contact_mobile_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'contact_mobile_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'contact_email' => [
                'sometimes', 'nullable', 'email', 'max:255',
                Rule::unique('clients', 'contact_email')
                    ->ignore($clientId)
                    ->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                    ),
            ],

            /* =======================
             * RELATIONS
             * ======================= */

            'salesperson_id' => [
                'sometimes', 'nullable',
                Rule::exists('users', 'id')
                    ->where('company_id', $companyId),
            ],

//            'payment_term_id' => [
//                'sometimes', 'nullable',
//                Rule::exists('payment_terms', 'id')
//                    ->where('company_id', $companyId),
//            ],

//            'pricelist_id' => [
//                'sometimes', 'nullable',
//                Rule::exists('price_lists', 'id')
//                    ->where('company_id', $companyId),
//            ],

            /* =======================
             * FLAGS & SETTINGS
             * ======================= */

            'note' => ['sometimes', 'nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            'granted_discount' => ['sometimes', 'nullable', 'numeric'],
            'is_blocked' => ['sometimes', 'boolean'],
            'show_on_pos' => ['sometimes', 'boolean'],
            'is_cash_customer' => ['sometimes', 'boolean'],
            'auto_generated_number' => ['sometimes', 'boolean'],

            'addresses' => ['sometimes', 'array'],


            'addresses.*.type' => ['required_with:addresses', 'integer'],
            'addresses.*.name' => ['sometimes', 'string', 'max:255'],
            'addresses.*.title' => ['sometimes', 'string', 'max:255'],
            'addresses.*.job_position' => ['sometimes', 'string', 'max:255'],
            'addresses.*.street' => ['sometimes', 'string', 'max:255'],
            'addresses.*.city' => ['sometimes', 'string', 'max:255'],
            'addresses.*.country' => ['sometimes', 'string', 'max:255'],
            'addresses.*.phone_code' => ['sometimes', 'string', 'max:10'],
            'addresses.*.phone_number' => ['sometimes', 'string', 'max:30'],
            'addresses.*.mobile_code' => ['sometimes', 'string', 'max:10'],
            'addresses.*.mobile_number' => ['sometimes', 'string', 'max:30'],
            'addresses.*.email' => ['sometimes', 'email', 'max:255'],
            'addresses.*.note' => ['sometimes', 'string'],
            'addresses.*.extension' => ['sometimes', 'string'],
            /* =======================
             * CARS
             * ======================= */

            'cars' => ['sometimes', 'array'],


            'cars.*.car_brand_id' => ['sometimes', 'exists:car_brands,id'],
            'cars.*.car_model_id' => ['sometimes', 'exists:car_models,id'],
            'cars.*.car_color_id' => ['sometimes', 'exists:car_colors,id'],
            'cars.*.car_technician_id' => ['sometimes', 'exists:car_technicians,id'],
            'cars.*.plate_number' => ['sometimes', 'string', 'max:50'],
            'cars.*.chassis_number' => ['sometimes', 'string', 'max:100'],
            'cars.*.car_fax' => ['sometimes', 'string', 'max:100'],
            'cars.*.year' => ['sometimes', 'integer', 'min:1900', 'max:' . now()->year],
            'cars.*.rating' => ['sometimes', 'string', 'max:50'],
            'cars.*.odometer' => ['sometimes', 'numeric'],
            'cars.*.comment' => ['sometimes', 'string'],
        ];

    }
}

<?php

namespace App\Http\Requests\ClientRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        $rules = [
            'type'   => ['required', 'string'],
            'name'   => ['required', 'string', 'max:255'],

            'client_number' => [
                'sometimes',
                'nullable',
                'string',
                Rule::unique('clients', 'client_number')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'client_company_id' => [
                'sometimes',
                Rule::exists('clients', 'id')
                    ->where('type', 'company')
                    ->where('company_id', $companyId),
            ],

            'country' => ['sometimes','nullable','string','max:255'],
            'city' => ['sometimes','nullable','string','max:255'],
            'state' => ['sometimes','nullable','string','max:255'],
            'zip' => ['sometimes','nullable','string','max:50'],
            'street' => ['sometimes','nullable','string','max:255'],
            'floor_and_building' => ['sometimes','nullable','string','max:255'],
            'job_position' => ['sometimes','nullable','string','max:255'],

            'phone_code' => ['sometimes','nullable','string','max:10'],
            'phone_number' => [
                'sometimes','nullable','string','max:50',
                Rule::unique('clients', 'phone_number')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'mobile_code' => ['sometimes','nullable','string','max:10'],
            'mobile_number' => [
                'sometimes','nullable','string','max:50',
                Rule::unique('clients', 'mobile_number')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'reference' => ['sometimes','nullable','string','max:255'],
            'email' => [
                'sometimes','nullable','email','max:255',
                Rule::unique('clients', 'email')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'title' => ['sometimes','nullable','string','max:255'],
            'tags' => ['sometimes','nullable','string'],
            'tax_id' => [
                'sometimes','nullable','string','max:255',
                Rule::unique('clients', 'tax_id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],
            'website' => ['sometimes','nullable','string','max:255'],

            'contact_type' => ['sometimes','nullable','string'],
            'contact_name' => ['sometimes','nullable','string','max:255'],
            'contact_country' => ['sometimes','nullable','string','max:255'],
            'contact_city' => ['sometimes','nullable','string','max:255'],
            'contact_state' => ['sometimes','nullable','string','max:255'],
            'contact_zip' => ['sometimes','nullable','string','max:50'],
            'contact_street' => ['sometimes','nullable','string','max:255'],
            'contact_phone_code' => ['sometimes','nullable','string','max:10'],
            'contact_phone_number' => ['sometimes','nullable','string','max:50'],
            'contact_mobile_code' => ['sometimes','nullable','string','max:10'],
            'contact_mobile_number' => ['sometimes','nullable','string','max:50'],
            'contact_email' => [
                'sometimes','nullable','email','max:255',
                Rule::unique('clients', 'contact_email')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'salesperson_id' => [
                'sometimes','nullable',
                Rule::exists('users', 'id')
                    ->where('company_id', $companyId),
            ],

//            'payment_term_id' => [
//                'sometimes','nullable',
//                Rule::exists('payment_terms','id')
//                    ->where('company_id', $companyId),
//            ],

            'pricelist_id' => [
                'sometimes','nullable',
                Rule::exists('price_lists','id')
                    ->where('company_id', $companyId),
            ],

            'note' => ['sometimes','nullable','string'],

            'active' => ['sometimes','boolean'],
            'granted_discount' => ['sometimes','nullable','numeric'],
            'is_blocked' => ['sometimes','boolean'],
            'show_on_pos' => ['sometimes','boolean'],
            'is_cash_customer' => ['sometimes','boolean'],
            'auto_generated_number' => ['sometimes','boolean'],
        ];

        return $rules;
    }
}

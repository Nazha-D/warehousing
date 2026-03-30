<?php

namespace App\Http\Requests\CompanyHeaderRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyHeaderRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId=auth()->user()->company_id;
            $rules['trn'] = [ Rule::unique('company_headers', 'trn')];
        $rules['logo']=[ 'image','mimes:jpeg,jpg,png', 'max:2048' ];
        $rules['email'] = [

            'email',
            'max:255',
            Rule::unique('company_headers', 'email')
        ];
        $rules['website'] = [
            'string', 'max:255'
        ];
        $rules['full_company_name'] = [
            'string'
        ];
        $rules['address'] = [
            'string'
        ];
        $rules['bank_info'] = [
            'string'
        ];
        $rules['local_payments'] = [
            'string'
        ];
        $rules['vat'] = [
            'numeric'
        ];
        $rules['phone_code'] = [
            'string'
        ];
        $rules['phone_number'] = [
            'string',
            Rule::unique('company_headers', 'phone_number')
        ];
        $rules['mobile_code'] = [
            'string'
        ];
        $rules['mobile_number'] = [
            'string',
            Rule::unique('company_headers', 'mobile_number')
            ];

        $rules['header_name'] = [
            'required',
            'max:255',
            Rule::unique('company_headers', 'header_name')->where(function ($query) {
                return $query->where('company_id', auth()->user()->company_id );
            }),
            $rules['default_quotation_currency_id'] = [
                'nullable',


                    function ($attribute, $value, $fail) use ($companyId) {
                        $companyCurrencies = auth()->user()->company->currencies->pluck('id')->toArray();
                        if ($value && !in_array($value, $companyCurrencies)) {
                            $fail("The selected currency is invalid for your company.");
                        }
                    },
                ],
            $rules['company_subject_to_vat'] = [
                'sometimes','boolean'
            ]
        ];

        return $rules;
    }
}

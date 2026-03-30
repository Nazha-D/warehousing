<?php

namespace App\Http\Requests\CarRequests;

use App\Http\Requests\ApiRequest;

use Illuminate\Validation\Rule;

class StoreCarRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Adjust if using policies
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'clientId' => [

                Rule::exists('clients', 'id')->where('company_id', $companyId),
            ],
            'car_brand_id'      => 'required|exists:car_brands,id',
            'car_model_id'      => 'required|exists:car_models,id',
            'car_color_id'      => 'required|exists:car_colors,id',
            'car_technician_id' => [
                'nullable',
                Rule::exists('car_technicians', 'id')->where('company_id', $companyId),
            ],
            'plate_number' => [
                'required',
                'string',
                Rule::unique('cars','plate_number')->where(function ($q) use ($companyId) {
                    return $q->whereIn('client_id', \App\Models\Client::where('company_id', $companyId)->pluck('id'));
                }),
            ],
            'chassis_number' => 'nullable|string|unique:cars,chassis_number',
            'car_fax'        => 'nullable|string',
            'year'           => 'nullable|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'rating'         => 'nullable|in:VIP,Regular,Blocked',
            'odometer'       => 'nullable|integer|min:0',
            'comment'        => 'nullable|string',
        ];
    }
}

<?php

namespace App\Http\Requests\CarRequests;

use App\Http\Requests\ApiRequest;

use Illuminate\Validation\Rule;

class UpdateCarRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $carId = $this->input('id') ?? optional($this->route('car'))->id;
        $companyId = auth()->user()->company_id;

        return [
            'clientId' => [
                'sometimes',
                Rule::exists('clients', 'id')->where('company_id', $companyId),
            ],
            'car_brand_id'      => 'sometimes|exists:car_brands,id',
            'car_model_id'      => 'sometimes|exists:car_models,id',
            'car_color_id'      => 'sometimes|exists:car_colors,id',
            'car_technician_id' => [
                'sometimes',
                Rule::exists('car_technicians', 'id')->where('company_id', $companyId),
            ],
            'plate_number' => [
                'sometimes',
                'string',
                Rule::unique('cars','plate_number')->ignore($carId)->where(function ($q) use ($companyId) {
                    return $q->whereIn('client_id', \App\Models\Client::where('company_id', $companyId)->pluck('id'));
                }),
            ],
            'chassis_number' => [
                'sometimes',
                'string',
                Rule::unique('cars', 'chassis_number')->ignore($carId),
            ],
            'car_fax'   => 'nullable|string',
            'year'      => 'sometimes|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'rating'    => 'sometimes|in:VIP,Regular,Blocked',
            'odometer'  => 'nullable|integer|min:0',
            'comment'   => 'nullable|string',
        ];
    }
}

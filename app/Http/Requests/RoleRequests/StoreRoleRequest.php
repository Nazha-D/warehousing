<?php

namespace App\Http\Requests\RoleRequests;

use App\Http\Requests\ApiRequest;

class StoreRoleRequest extends ApiRequest
{
    public function authorize()
    {
        return true; // أو ضع منطق صلاحية السوبر أدمين
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',

        ];
    }
}

<?php

namespace App\Http\Requests\RoleRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $role = $this->route('role');
        $roleId = $role instanceof \App\Models\Role ? $role->id : $role;

        return [
            'name' => [
                'nullable',
                'string',
                Rule::unique('roles')->ignore($roleId)->where(function ($query) use ($role) {
                    return $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }
}

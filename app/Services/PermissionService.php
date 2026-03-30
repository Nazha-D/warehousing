<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;

class PermissionService{

    public static function getAll( $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $nameDefault = '';

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $name = $options['search'] ?? $nameDefault;

        $permissions = Permission::query();

      //  $permissions = $permissions->where('company_id', $userCompanyId);
        $permissions=$permissions->where('name', 'like', '%'.$name.'%');

        if ($isPaginated) {
            $permissions = $permissions->paginate($perPage);
        } else {
            $permissions = $permissions->get();
        }


        return $permissions;
    }

}

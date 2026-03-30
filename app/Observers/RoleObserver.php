<?php

namespace App\Observers;

use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleObserver
{
    public function creating(Role $role)
    {


    }
    public function updated()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

}

<?php
namespace App\Policies;

use App\Models\User;

class BaseModelPolicy
{
    /**
     * Override logic for super admins.
     */

    /**
     * viewAny – permission to see list
     */
    public function viewAny(User $user)
    {
        if ($user->roles()->where('name', 'super-admin')->exists()) {
            return true;
        }
        return $user->hasPermissionTo( $this->permissionName().".view" );
    }

    /**
     * view single model
     */
    public function view(User $user, $model)
    {
        return
            $user->company_id === $model->company_id &&
            $user->hasPermissionTo($this->permissionName().".view" );
    }

    /**
     * create
     */
    public function create(User $user)
    {
        return (bool)$user->hasPermissionTo($this->permissionName().".create");
    }

    /**
     * update
     */
    public function update(User $user, $model)
    {
        return
            $user->company_id === $model->company_id &&
            $user->hasPermissionTo($this->permissionName().".update");
    }

    /**
     * delete
     */
    public function delete(User $user, $model)
    {
        return
            $user->company_id === $model->company_id &&
            $user->hasPermissionTo($this->permissionName().".delete");
    }


    public function restore(User $user, $model)
    {
        return
            $user->company_id === $model->company_id &&
            $user->hasPermissionTo($this->permissionName().".restore");
    }

    public  function forceDelete(User $user,$model)
    {
        return
            $user->company_id === $model->company_id &&
            $user->hasPermissionTo($this->permissionName().".force_delete");
    }
    /**
     * Helper: permission root name
     */
    protected function permissionName()
    {
        // Override this in child policies
        return '';
    }
}

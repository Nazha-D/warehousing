<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Auth\Access\Response;

class WarehousePolicy extends  BaseModelPolicy
{
    /**
     * Determine whether the user can view any models.
     */


    protected function isSuperAdminOfModel(User $user, $model = null): bool
    {
        if ($user->roles()->where('name', 'super-admin')->exists()) {
            if ($model && isset($model->company_id)) {
                return $model->company_id === $user->company_id;
            }
            return true;
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::viewAny($user);
    }

    public function view(User $user, $warehouse): bool
    {
        if ($this->isSuperAdminOfModel($user, $warehouse)) {
            return true;
        }

        return parent::view($user, $warehouse);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $warehouse): bool
    {
        if ($this->isSuperAdminOfModel($user, $warehouse)) {
            return true;
        }

        return parent::update($user, $warehouse);
    }

    public function delete(User $user, $warehouse): bool
    {
        if ($this->isSuperAdminOfModel($user, $warehouse)) {
            return true;
        }

        return parent::delete($user, $warehouse);
    }

    public function restore(User $user, $warehouse): bool
    {
        if ($this->isSuperAdminOfModel($user, $warehouse)) {
            return true;
        }

        return parent::restore($user, $warehouse);
    }

    public function forceDelete(User $user, $warehouse): bool
    {
        if ($this->isSuperAdminOfModel($user, $warehouse)) {
            return true;
        }

        return parent::forceDelete($user, $warehouse);
    }

    protected function permissionName()
    {
        return 'warehouse';
    }
}

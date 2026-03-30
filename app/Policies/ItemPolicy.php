<?php

namespace App\Policies;

use App\Models\User;

class ItemPolicy extends  BaseModelPolicy
{

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

    public function view(User $user, $itemGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $itemGroup)) {
            return true;
        }

        return parent::view($user, $itemGroup);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $itemGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $itemGroup)) {
            return true;
        }

        return parent::update($user, $itemGroup);
    }

    public function delete(User $user, $itemGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $itemGroup)) {
            return true;
        }

        return parent::delete($user, $itemGroup);
    }

    public function restore(User $user, $itemGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $itemGroup)) {
            return true;
        }

        return parent::restore($user, $itemGroup);
    }

    public function forceDelete(User $user, $itemGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $itemGroup)) {
            return true;
        }

        return parent::forceDelete($user, $itemGroup);
    }


    protected function permissionName()
    {
        return 'item';
    }
}

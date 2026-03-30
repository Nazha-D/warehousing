<?php

namespace App\Policies;

use App\Models\User;

class CurrencyPolicy extends  BaseModelPolicy
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

    public function view(User $user, $currency): bool
    {
        if ($this->isSuperAdminOfModel($user, $currency)) {
            return true;
        }

        return parent::view($user, $currency);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $currency): bool
    {
        if ($this->isSuperAdminOfModel($user, $currency)) {
            return true;
        }

        return parent::update($user, $currency);
    }

    public function delete(User $user, $currency): bool
    {
        if ($this->isSuperAdminOfModel($user, $currency)) {
            return true;
        }

        return parent::delete($user, $currency);
    }

    public function restore(User $user, $currency): bool
    {
        if ($this->isSuperAdminOfModel($user, $currency)) {
            return true;
        }

        return parent::restore($user, $currency);
    }

    public function forceDelete(User $user, $currency): bool
    {
        if ($this->isSuperAdminOfModel($user, $currency)) {
            return true;
        }

        return parent::forceDelete($user, $currency);
    }


    protected function permissionName()
    {
        return 'currency';
    }
}

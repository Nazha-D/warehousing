<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BaseModelPolicy
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

    public function view(User $user, $userModel): bool
    {
        if ($this->isSuperAdminOfModel($user, $userModel)) {
            return true;
        }

        return parent::view($user, $userModel);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $userModel): bool
    {
        if ($this->isSuperAdminOfModel($user, $userModel)) {
            return true;
        }

        return parent::update($user, $userModel);
    }

    public function delete(User $user, $userModel): bool
    {
        if ($this->isSuperAdminOfModel($user, $userModel)) {
            return true;
        }

        return parent::delete($user, $userModel);
    }

    public function restore(User $user, $userModel): bool
    {
        if ($this->isSuperAdminOfModel($user, $userModel)) {
            return true;
        }

        return parent::restore($user, $userModel);
    }

    public function forceDelete(User $user, $userModel): bool
    {
        if ($this->isSuperAdminOfModel($user, $userModel)) {
            return true;
        }

        return parent::forceDelete($user, $userModel);
    }


    protected function permissionName()
    {
        return 'user';
    }
}

<?php

namespace App\Policies;

use App\Models\Combo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ComboPolicy extends BaseModelPolicy

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

    public function view(User $user, $combo): bool
    {
        if ($this->isSuperAdminOfModel($user, $combo)) {
            return true;
        }

        return parent::view($user, $combo);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $combo): bool
    {
        if ($this->isSuperAdminOfModel($user, $combo)) {
            return true;
        }

        return parent::update($user, $combo);
    }

    public function delete(User $user, $combo): bool
    {
       if ($this->isSuperAdminOfModel($user, $combo)) {
            return true;
        }

        return parent::delete($user, $combo);
    }

    public function restore(User $user, $combo): bool
    {
        if ($this->isSuperAdminOfModel($user, $combo)) {
            return true;
        }

        return parent::restore($user, $combo);
    }

    public function forceDelete(User $user, $combo): bool
    {
        if ($this->isSuperAdminOfModel($user, $combo)) {
            return true;
        }

        return parent::forceDelete($user, $combo);
    }

    protected function permissionName()
    {
        return 'combo';
    }
}

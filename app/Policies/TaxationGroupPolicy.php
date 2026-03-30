<?php

namespace App\Policies;

use App\Models\TaxationGroup;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaxationGroupPolicy extends BaseModelPolicy
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

    public function view(User $user, $taxationGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxationGroup)) {
            return true;
        }

        return parent::view($user, $taxationGroup);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $taxationGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxationGroup)) {
            return true;
        }

        return parent::update($user, $taxationGroup);
    }

    public function delete(User $user, $taxationGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxationGroup)) {
            return true;
        }

        return parent::delete($user, $taxationGroup);
    }

    public function restore(User $user, $taxationGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxationGroup)) {
            return true;
        }

        return parent::restore($user, $taxationGroup);
    }

    public function forceDelete(User $user, $taxationGroup): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxationGroup)) {
            return true;
        }

        return parent::forceDelete($user, $taxationGroup);
    }

    protected function permissionName()
    {
        return 'taxation_group';
    }
}

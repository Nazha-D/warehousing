<?php

namespace App\Policies;

use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaxRatePolicy extends  BaseModelPolicy
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

    public function view(User $user, $taxRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxRate)) {
            return true;
        }

        return parent::view($user, $taxRate);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $taxRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxRate)) {
            return true;
        }

        return parent::update($user, $taxRate);
    }

    public function delete(User $user, $taxRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxRate)) {
            return true;
        }

        return parent::delete($user, $taxRate);
    }

    public function restore(User $user, $taxRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxRate)) {
            return true;
        }

        return parent::restore($user, $taxRate);
    }

    public function forceDelete(User $user, $taxRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $taxRate)) {
            return true;
        }

        return parent::forceDelete($user, $taxRate);
    }


    protected function permissionName()
    {
        return 'tax_rate';
    }
}

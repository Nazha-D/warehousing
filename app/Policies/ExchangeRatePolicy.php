<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ExchangeRatePolicy extends BaseModelPolicy
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

    public function view(User $user, $exchangeRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $exchangeRate)) {
            return true;
        }

        return parent::view($user, $exchangeRate);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $exchangeRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $exchangeRate)) {
            return true;
        }

        return parent::update($user, $exchangeRate);
    }

    public function delete(User $user, $exchangeRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $exchangeRate)) {
            return true;
        }

        return parent::delete($user, $exchangeRate);
    }

    public function restore(User $user, $exchangeRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $exchangeRate)) {
            return true;
        }

        return parent::restore($user, $exchangeRate);
    }

    public function forceDelete(User $user, $exchangeRate): bool
    {
        if ($this->isSuperAdminOfModel($user, $exchangeRate)) {
            return true;
        }

        return parent::forceDelete($user, $exchangeRate);
    }


    protected function permissionName()
    {
        return 'exchange_rate';
    }
}

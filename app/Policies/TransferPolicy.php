<?php

namespace App\Policies;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransferPolicy extends BaseModelPolicy
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

    public function view(User $user, $transfer): bool
    {
        if ($this->isSuperAdminOfModel($user, $transfer)) {
            return true;
        }

        return parent::view($user, $transfer);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $transfer): bool
    {
        if ($this->isSuperAdminOfModel($user, $transfer)) {
            return true;
        }

        return parent::update($user, $transfer);
    }

    public function delete(User $user, $transfer): bool
    {
        if ($this->isSuperAdminOfModel($user, $transfer)) {
            return true;
        }

        return parent::delete($user, $transfer);
    }

    public function restore(User $user, $transfer): bool
    {
        if ($this->isSuperAdminOfModel($user, $transfer)) {
            return true;
        }

        return parent::restore($user, $transfer);
    }

    public function forceDelete(User $user, $transfer): bool
    {
        if ($this->isSuperAdminOfModel($user, $transfer)) {
            return true;
        }

        return parent::forceDelete($user, $transfer);
    }

    protected function permissionName()
    {
        return 'transfer';
    }
}

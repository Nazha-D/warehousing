<?php

namespace App\Policies;

use App\Models\User;

class PosInvoicePolicy extends BaseModelPolicy
{
    /**
     * Create a new policy instance.
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

    public function view(User $user, $posInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $posInvoice)) {
            return true;
        }

        return parent::view($user, $posInvoice);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $posInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $posInvoice)) {
            return true;
        }

        return parent::update($user, $posInvoice);
    }

    public function delete(User $user, $posInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $posInvoice)) {
            return true;
        }

        return parent::delete($user, $posInvoice);
    }

    public function restore(User $user, $posInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $posInvoice)) {
            return true;
        }

        return parent::restore($user, $posInvoice);
    }

    public function forceDelete(User $user, $posInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $posInvoice)) {
            return true;
        }

        return parent::forceDelete($user, $posInvoice);
    }

    protected function permissionName()
    {
        return 'pos_invoice';
    }
}

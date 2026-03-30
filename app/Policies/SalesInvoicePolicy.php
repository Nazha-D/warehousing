<?php

namespace App\Policies;

use App\Models\User;

class SalesInvoicePolicy extends  BaseModelPolicy
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

    public function view(User $user, $salesInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesInvoice)) {
            return true;
        }

        return parent::view($user, $salesInvoice);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $salesInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesInvoice)) {
            return true;
        }

        return parent::update($user, $salesInvoice);
    }

    public function delete(User $user, $salesInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesInvoice)) {
            return true;
        }

        return parent::delete($user, $salesInvoice);
    }

    public function restore(User $user, $salesInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesInvoice)) {
            return true;
        }

        return parent::restore($user, $salesInvoice);
    }

    public function forceDelete(User $user, $salesInvoice): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesInvoice)) {
            return true;
        }

        return parent::forceDelete($user, $salesInvoice);
    }

    protected function permissionName()
    {
        return 'sales_invoice';
    }
}

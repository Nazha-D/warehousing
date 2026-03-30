<?php

namespace App\Policies;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalesOrderPolicy extends  BaseModelPolicy
{
    /**
     * Determine whether the user can view any models.
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

    public function view(User $user, $salesOrder): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesOrder)) {
            return true;
        }

        return parent::view($user, $salesOrder);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $salesOrder): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesOrder)) {
            return true;
        }

        return parent::update($user, $salesOrder);
    }

    public function delete(User $user, $salesOrder): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesOrder)) {
            return true;
        }

        return parent::delete($user, $salesOrder);
    }

    public function restore(User $user, $salesOrder): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesOrder)) {
            return true;
        }

        return parent::restore($user, $salesOrder);
    }

    public function forceDelete(User $user, $salesOrder): bool
    {
        if ($this->isSuperAdminOfModel($user, $salesOrder)) {
            return true;
        }

        return parent::forceDelete($user, $salesOrder);
    }
    public function editLinePrice(User $user): bool
    {
        return $user->can('sales_order.edit_price');
    }

    public function editLineDescription(User $user): bool
    {
        return  $user->can('sales_order.edit_description');
    }

    protected function permissionName()
    {
        return 'sales_order';
    }
}

<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DeliveryPolicy extends  BaseModelPolicy
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

    public function view(User $user, $delivery): bool
    {
        if ($this->isSuperAdminOfModel($user, $delivery)) {
            return true;
        }

        return parent::view($user, $delivery);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $delivery): bool
    {
        if ($this->isSuperAdminOfModel($user, $delivery)) {
            return true;
        }

        return parent::update($user, $delivery);
    }

    public function delete(User $user, $delivery): bool
    {
        if ($this->isSuperAdminOfModel($user, $delivery)) {
            return true;
        }

        return parent::delete($user, $delivery);
    }

    public function restore(User $user, $delivery): bool
    {
        if ($this->isSuperAdminOfModel($user, $delivery)) {
            return true;
        }

        return parent::restore($user, $delivery);
    }

    public function forceDelete(User $user, $delivery): bool
    {
        if ($this->isSuperAdminOfModel($user, $delivery)) {
            return true;
        }

        return parent::forceDelete($user, $delivery);
    }

    protected function permissionName()
    {
        return 'delivery';
    }
}

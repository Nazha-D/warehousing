<?php

namespace App\Policies;

use App\Models\Replenishment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReplenishmentPolicy extends BaseModelPolicy
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

    public function view(User $user, $replenishment): bool
    {
        if ($this->isSuperAdminOfModel($user, $replenishment)) {
            return true;
        }

        return parent::view($user, $replenishment);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $replenishment): bool
    {
        if ($this->isSuperAdminOfModel($user, $replenishment)) {
            return true;
        }

        return parent::update($user, $replenishment);
    }

    public function delete(User $user, $replenishment): bool
    {
        if ($this->isSuperAdminOfModel($user, $replenishment)) {
            return true;
        }

        return parent::delete($user, $replenishment);
    }

    public function restore(User $user, $replenishment): bool
    {
        if ($this->isSuperAdminOfModel($user, $replenishment)) {
            return true;
        }

        return parent::restore($user, $replenishment);
    }

    public function forceDelete(User $user, $replenishment): bool
    {
        if ($this->isSuperAdminOfModel($user, $replenishment)) {
            return true;
        }

        return parent::forceDelete($user, $replenishment);
    }

    protected function permissionName()
    {
        return 'replenishment';
    }
}

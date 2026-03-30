<?php

namespace App\Policies;

use App\Models\PosSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PosSessionPolicy extends  BaseModelPolicy
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

    public function view(User $user, $posSession): bool
    {
        if ($this->isSuperAdminOfModel($user, $posSession)) {
            return true;
        }

        return parent::view($user, $posSession);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $posSession): bool
    {
        if ($this->isSuperAdminOfModel($user, $posSession)) {
            return true;
        }

        return parent::update($user, $posSession);
    }

    public function delete(User $user, $posSession): bool
    {
        if ($this->isSuperAdminOfModel($user, $posSession)) {
            return true;
        }

        return parent::delete($user, $posSession);
    }

    public function restore(User $user, $posSession): bool
    {
        if ($this->isSuperAdminOfModel($user, $posSession)) {
            return true;
        }

        return parent::restore($user, $posSession);
    }

    public function forceDelete(User $user, $posSession): bool
    {
        if ($this->isSuperAdminOfModel($user, $posSession)) {
            return true;
        }

        return parent::forceDelete($user, $posSession);
    }

    protected function permissionName()
    {
        return 'pos_session';
    }
}

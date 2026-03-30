<?php

namespace App\Policies;

use App\Models\PosTerminal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PosTerminalPolicy extends  BaseModelPolicy
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

    public function view(User $user, $posTerminal): bool
    {
        if ($this->isSuperAdminOfModel($user, $posTerminal)) {
            return true;
        }

        return parent::view($user, $posTerminal);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $posTerminal): bool
    {
        if ($this->isSuperAdminOfModel($user, $posTerminal)) {
            return true;
        }

        return parent::update($user, $posTerminal);
    }

    public function delete(User $user, $posTerminal): bool
    {
        if ($this->isSuperAdminOfModel($user, $posTerminal)) {
            return true;
        }

        return parent::delete($user, $posTerminal);
    }

    public function restore(User $user, $posTerminal): bool
    {
        if ($this->isSuperAdminOfModel($user, $posTerminal)) {
            return true;
        }

        return parent::restore($user, $posTerminal);
    }

    public function forceDelete(User $user, $posTerminal): bool
    {
        if ($this->isSuperAdminOfModel($user, $posTerminal)) {
            return true;
        }

        return parent::forceDelete($user, $posTerminal);
    }

    protected function permissionName()
    {
        return 'pos_terminal';
    }
}

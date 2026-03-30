<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy extends BaseModelPolicy
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

    public function view(User $user, $client): bool
    {
        if ($this->isSuperAdminOfModel($user, $client)) {
            return true;
        }

        return parent::view($user, $client);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $client): bool
    {
        if ($this->isSuperAdminOfModel($user, $client)) {
            return true;
        }

        return parent::update($user, $client);
    }

    public function delete(User $user, $client): bool
    {
        if ($this->isSuperAdminOfModel($user, $client)) {
            return true;
        }

        return parent::delete($user, $client);
    }

    public function restore(User $user, $client): bool
    {
        if ($this->isSuperAdminOfModel($user, $client)) {
            return true;
        }

        return parent::restore($user, $client);
    }

    public function forceDelete(User $user, $client): bool
    {
        if ($this->isSuperAdminOfModel($user, $client)) {
            return true;
        }

        return parent::forceDelete($user, $client);
    }

    protected function permissionName()
    {
        return 'client';
    }
}

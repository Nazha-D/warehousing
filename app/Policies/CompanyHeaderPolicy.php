<?php

namespace App\Policies;

use App\Models\User;

class CompanyHeaderPolicy extends BaseModelPolicy
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

    public function view(User $user, $companyHeader): bool
    {
        if ($this->isSuperAdminOfModel($user, $companyHeader)) {
            return true;
        }

        return parent::view($user, $companyHeader);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $companyHeader): bool
    {
        if ($this->isSuperAdminOfModel($user, $companyHeader)) {
            return true;
        }

        return parent::update($user, $companyHeader);
    }

    public function delete(User $user, $companyHeader): bool
    {
        if ($this->isSuperAdminOfModel($user, $companyHeader)) {
            return true;
        }

        return parent::delete($user, $companyHeader);
    }

    public function restore(User $user, $companyHeader): bool
    {
        if ($this->isSuperAdminOfModel($user, $companyHeader)) {
            return true;
        }

        return parent::restore($user, $companyHeader);
    }

    public function forceDelete(User $user, $companyHeader): bool
    {
        if ($this->isSuperAdminOfModel($user, $companyHeader)) {
            return true;
        }

        return parent::forceDelete($user, $company_header);
    }

    protected function permissionName()
    {
        return 'company_header';
    }
}

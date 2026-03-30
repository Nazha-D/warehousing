<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy extends BaseModelPolicy
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

    public function view(User $user, $category): bool
    {
        if ($this->isSuperAdminOfModel($user, $category)) {
            return true;
        }

        return parent::view($user, $category);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $category): bool
    {
        if ($this->isSuperAdminOfModel($user, $category)) {
            return true;
        }

        return parent::update($user, $category);
    }

    public function delete(User $user, $category): bool
    {
        if ($this->isSuperAdminOfModel($user, $category)) {
            return true;
        }

        return parent::delete($user, $category);
    }

    public function restore(User $user, $category): bool
    {
        if ($this->isSuperAdminOfModel($user, $category)) {
            return true;
        }

        return parent::restore($user, $category);
    }

    public function forceDelete(User $user, $category): bool
    {
        if ($this->isSuperAdminOfModel($user, $category)) {
            return true;
        }

        return parent::forceDelete($user, $category);
    }

    protected function permissionName()
    {
        return 'category';
    }
}

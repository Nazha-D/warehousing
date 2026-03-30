<?php

namespace App\Policies;

use App\Models\PriceList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PriceListPolicy extends BaseModelPolicy
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

    public function view(User $user, $priceList): bool
    {
        if ($this->isSuperAdminOfModel($user, $priceList)) {
            return true;
        }

        return parent::view($user, $priceList);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $priceList): bool
    {
        if ($this->isSuperAdminOfModel($user, $priceList)) {
            return true;
        }

        return parent::update($user, $priceList);
    }

    public function delete(User $user, $priceList): bool
    {
        if ($this->isSuperAdminOfModel($user, $priceList)) {
            return true;
        }

        return parent::delete($user, $priceList);
    }

    public function restore(User $user, $priceList): bool
    {
        if ($this->isSuperAdminOfModel($user, $priceList)) {
            return true;
        }

        return parent::restore($user, $priceList);
    }

    public function forceDelete(User $user, $priceList): bool
    {
        if ($this->isSuperAdminOfModel($user, $priceList)) {
            return true;
        }

        return parent::forceDelete($user, $priceList);
    }

    protected function permissionName()
    {
        return 'priceList';
    }
}

<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuotationPolicy extends BaseModelPolicy
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

    public function view(User $user, $quotation): bool
    {
        if ($this->isSuperAdminOfModel($user, $quotation)) {
            return true;
        }

        return parent::view($user, $quotation);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdminOfModel($user)) {
            return true;
        }

        return parent::create($user);
    }

    public function update(User $user, $quotation): bool
    {
        if ($this->isSuperAdminOfModel($user, $quotation)) {
            return true;
        }

        return parent::update($user, $quotation);
    }

    public function delete(User $user, $quotation): bool
    {
        if ($this->isSuperAdminOfModel($user, $quotation)) {
            return true;
        }

        return parent::delete($user, $quotation);
    }

    public function restore(User $user, $quotation): bool
    {
        if ($this->isSuperAdminOfModel($user, $quotation)) {
            return true;
        }

        return parent::restore($user, $quotation);
    }

    public function forceDelete(User $user, $quotation): bool
    {
        if ($this->isSuperAdminOfModel($user, $quotation)) {
            return true;
        }

        return parent::forceDelete($user, $quotation);
    }
    public function editLinePrice(User $user): bool
    {
        return  $user->can('quotation.edit_price');
    }

    public function editLineDescription(User $user): bool
    {
        return  $user->can('quotation.edit_description');
    }

    protected function permissionName()
    {
        return 'quotation';
    }
}

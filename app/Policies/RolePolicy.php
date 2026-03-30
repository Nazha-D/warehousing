<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;

class RolePolicy
{
    /**
     * تحقق إذا كان المستخدم سوبر ادمن وللموديل نفس الشركة
     */
    protected function isSuperAdminOfModel(User $user, $model = null): bool
    {
        // فقط super-admin
        if ($user->roles()->where('name', 'super-admin')->exists()) {
            // إذا يوجد موديل، تحقق من الشركة
            if ($model && isset($model->company_id)) {
                return $model->company_id === $user->company_id;
            }
            // إذا لا يوجد موديل (مثلاً viewAny أو create)
            return true;
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        return $this->isSuperAdminOfModel($user);
    }

    public function view(User $user, $role): bool
    {
        return $this->isSuperAdminOfModel($user, $role);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdminOfModel($user);
    }

    public function update(User $user, $role): bool
    {
        return $this->isSuperAdminOfModel($user, $role);
    }

    public function delete(User $user, $role): bool
    {
        return $this->isSuperAdminOfModel($user, $role);
    }

    public function restore(User $user, $role): bool
    {
        return $this->isSuperAdminOfModel($user, $role);
    }

    public function forceDelete(User $user, $role): bool
    {
        return $this->isSuperAdminOfModel($user, $role);
    }

    /**
     * اسم الصلاحية المرتبطة بالـ Role (إذا استخدم في BaseModelPolicy)
     */
    protected function permissionName()
    {
        return 'role';
    }
}

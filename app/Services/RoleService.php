<?php
namespace App\Services;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


class RoleService
{
    public static function getAll( $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $searchDefault = '';


        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;


        $roles = Role::query();


            $roles->where('company_id', $userCompanyId);
        $roles->filter($search);



        //$roles->filter($search);


        if ($isPaginated) {
            $roles = $roles->with('permissions')->paginate($perPage);
        } else {
//            $roles = $roles->with(['permissions' => function ($query) use ($search) {
//                $query->where('name', 'like', $search); // Load only permissions that match "edit"
//            }])->get();
            $roles = $roles->with('permissions')->get();
        }

        return $roles->reject(fn ($role) => $role->name === 'super-admin')->values();
    }

    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'company_id' => auth()->user()->company_id,
            ]);

            if (!empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->load('permissions');
        });
    }

    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $role->update([
                Arr::get($data, 'name', $role->name),
              //  'company_id' => $data['company_id'] ?? $role->company_id,
            ]);
            $name= Arr::get($data, 'name');
            $role->update(['name'=>$name]);
            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->load('permissions');
        });
    }

    public function delete(Role $role): void
    {
        DB::transaction(function () use ($role) {

            $role->delete();
        });
    }
}

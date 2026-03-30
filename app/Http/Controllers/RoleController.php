<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequests\StoreRoleRequest;
use App\Http\Requests\RoleRequests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use ApiResponseTrait;

    protected $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {

        try {
            $this->authorize('viewAny', Role::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search' => $request->query('search'),
                'onlyActive' => $request->query('onlyActive'),
            ];
            $roles = RoleService::getAll( $user->company_id, $options);

            return $this->successResponse($roles, 'Roles are here', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function show(Request $request, Role $role)
    {
        try {
            $this->authorize('view', $role);

            $permissionSearch = $request->query('search');

            $role->load(['permissions' => function ($query) use ($permissionSearch) {
                if ($permissionSearch) {
                    $query->where('name', 'LIKE', '%' . $permissionSearch . '%');
                }
            }]);

            return $this->successResponse($role, 'Role fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function store(StoreRoleRequest $request)
    {
        try {

            $this->authorize('create',Role::class);

            $role = $this->service->create($request->validated());
            return $this->successResponse($role, 'Role created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            $this->authorize('update',$role);
            $role = $this->service->update($role, $request->validated());

            return $this->successResponse($role, 'Role updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(Role $role)
    {
        try {
            $this->authorize('delete',$role);
            $this->service->delete($role);

            return $this->successResponse([], 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }


    public function getPermissions(Request $request)
    {

        try {
        $this->authorize('viewAny', Role::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search' => $request->query('search'),
                'onlyActive' => $request->query('onlyActive'),
            ];
          $permissions = PermissionService::getAll(  $options);

            return $this->successResponse($permissions, 'permissions are here', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

}

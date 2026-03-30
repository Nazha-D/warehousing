<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequests\StoreUserRequest;
use App\Http\Requests\UserRequests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\CashingMethod;
use Illuminate\Container\RewindableGenerator;
use Illuminate\Support\Facades\Auth;
use App\Models\CommissionMethod;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSalespersonConfiguration;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use ApiResponseTrait;
    /**
     *
     */
    public function index(Request $request)
    {
        try {
          $this->authorize('viewAny', User::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search' => $request->query('search'),
                'salesmen'=>$request->query('salesmen'),
            ];
            $users= UserService::getAll( $user->company_id, $options);
            $message = 'Users fetched successfully';

            return $this->successResponse(UserResource::collection($users),$message,200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(User $user,Request $request)
    {
        try {
            $request->merge(['detailed' => true]);
            if (!$user) {
                $message = 'User not found';

                return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
            }
            $this->authorize('view', $user);

            $message = 'User retrieved successfully.';
             $user=new UserResource(
                 $user->load([
                     'userSalespersonConfiguration',
                     'company',
                     'roles'
                 ])
             );
            return $this->successResponse($user, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create()
    {
        try {
            $this->authorize('create', User::class);
            $user = \Auth::user();
            $data['roles'] = Role::where('company_id', $user->company_id)->get(['id', 'name']);
           // $data['posTerminals'] = PosTerminal::where('company_id', $user->company_id)->get(['id', 'name']);

            $data['cashingMethods'] = CashingMethod::active()->get(['id', 'title']);
              $data['commissionMethods'] = CommissionMethod::active()->get(['id', 'title']);

            $message = 'Data needed for user creation retrieved successfully';

            return $this->successResponse($data, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreUserRequest $request)
    {

        DB::beginTransaction();
        try {

            $this->authorize('create', User::class);
            $authUser = auth()->user();

            $user = User::create(
              $request->validated()
            );
            $user->update(['company_id' => $authUser->company_id]);
            $roles = Role::WhereIn('id', $request->roles)->get()->pluck('id');
            $user->assignRole($roles);

            if ($request->is_salesperson) {
                UserSalespersonConfiguration::create([
                    'user_id' => $user->id,
                    'cashing_method_id' => $request->cashing_method_id,
                    'commission_method_id' => $request->commission_method_id,
                    'commission' => $request->commission,
                ]);
            }


            $message = 'User created successfully';

            DB::commit();

            return $this->successResponse($user, $message, 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                $message = 'User not found';

                return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
            }
            $this->authorize('update', $user);
            $authUser = Auth::user();

            $data['roles'] = Role::where('company_id', $authUser->company_id)->get(['id', 'name']);
            $data['cashingMethods'] = CashingMethod::active()->get(['id', 'title']);
            $data['commissionMethods'] = CommissionMethod::active()->get(['id', 'title']);
            $data['user'] = UserResource::make($user);

            $message = 'Data needed for user edit retrieved successfully';

            return $this->successResponse($data, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User not found.', Response::HTTP_NOT_FOUND);
            }
            $this->authorize('update', $user);

            $user->update(
                $request->validated()
            );

            if ($request->is_salesperson) {
                UserSalespersonConfiguration::updateOrCreate(
                    ['user_id' => $id],
                    [
                        'cashing_method_id' => $request->cashing_method_id,
                        'commission_method_id' => $request->commission_method_id,
                        'commission' => $request->commission,
                    ]
                );
            }

            //  $role = Role::firstWhere('id', $request->roleId);
            $roles = Role::WhereIn('id', $request->roles)->get();
            $user->syncRoles([$roles]);


            $message = 'User updated successfully';
            DB::commit();

            return $this->successResponse(new UserResource($user), $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User not found.', Response::HTTP_NOT_FOUND);
            }
            $this->authorize('delete', $user);

            $user->userSalespersonConfiguration()->delete();
            $user->delete();

            $message = 'User deleted successfully.';

            return $this->successResponse(null, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}


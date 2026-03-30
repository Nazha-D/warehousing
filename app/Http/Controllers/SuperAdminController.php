<?php

namespace App\Http\Controllers;

use App\Nova\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller

{
    use ApiResponseTrait;
    private $user=null;
    public function __construct()
    {
        $this->user=auth()->user();
        $this->middleware(['auth', 'role:super-admin']);
    }

    public function getRoles(Request $request)
    {
        $options=[
            'isPaginated'=>$request->query('isPaginated'),
            'perPage'=>$request->query('perPage'),
            'search'=>$request->query('search'),
        ];
        $data['roles']=RoleService::getAll($this->user->company_id,$options);
        $message='Roles fetched successfully';
        return $this->successResponse($data,$message,200);

    }

    public function addRole(Request  $request)
    {
        try{
            DB::beginTransaction();
            $user=auth()->user();
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('roles')->where(function ($query) use ($request,$user) {
                        return $query->where('company_id', $user->company_id);
                    })
                ],
            ]);
           $role= Role::create(['name'=>$request->name,
                          'guard_name'=>'web',
                           'company_id'=>$user->company_id
                ]);
            $message='Role created successfully';
            DB::commit();
            $this->successResponse($role,$message,201);
        }
        catch(\Exception $exception){
            DB::rollBack();
            $this->errorResponse($exception->getMessage(),500,[]);
        }
    }
    public function getUsers(Request $request)
    {
        $options=[
            'isPaginated'=>$request->query('isPaginated'),
            'perPage'=>$request->query('perPage'),
            'search'=>$request->query('search'),
        ];
        $data['users']=RoleService::getAll($this->user->company_id,$options);
        $message='Users fetched successfully';
        return $this->successResponse($data,$message,200);

    }


}

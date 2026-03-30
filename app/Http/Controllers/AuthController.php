<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequests\RegisterRequest;
use App\Http\Requests\AuthRequests\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use http\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;
    protected  $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $this->authorize('create',User::class);
           DB::beginTransaction();
            $user = $this->authService->register($request->validated());

            $message = 'User registered successfully';
            DB::commit();
           return $this->successResponse($user,$message,201);
        }
        catch(\Exception $exception)
        {
            DB::rollBack();

            return $this->errorResponse($exception->getMessage(), 500, []);
        }
    }

    public function login(LoginRequest $request)
    {
        try {

            DB::beginTransaction();
            $userData = $this->authService->login($request->validated());
            if (!$userData)
                return  $this->errorResponse('Incorrect password',422,[]);
            $token = $userData['token'];
            $message = '';

            if (!$token) {
                $message = 'Invalid credentials';
                return  $this->errorResponse($message,422,[]);

            }
            DB::commit();
            $message = 'User Logged In Successfully';
            //  return response()->success($message, $token , 200);
            $user = $userData['user'];

            $data['token'] = $token;
            $data['user'] = $user;
            return $this->successResponse($data, $message, 200);


        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->errorResponse($exception->getMessage(), 500, []);

        }
    }

    public function logout()
    {
        $this->authService->logout(auth()->user());
        $message='Logged out from current device successfully';
        return $this->successResponse([],$message,200);
    }

    public function logoutCurrent(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success'=>'true',
            'message'=>'Logged out from current device successfully',
            'data'=>[]],200);
    }
    public function logoutAllDevices(Request $request)
    {
        $this->authService->logoutAll($request->user());
        $message='Logged out from all devices successfully';
        return $this->successResponse([], $message, 200);

    }
}

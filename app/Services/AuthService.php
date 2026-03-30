<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'company_id' => $data['companyId'] ?? auth()->user()->company_id,
        ]);
        $roleModels = Role::whereIn('id', $data['roles'])->get();
        app(PermissionRegistrar::class)
            ->setPermissionsTeamId($user->company_id);
        foreach ($roleModels as $role) {
            $user->assignRole($role);
        }
        return $user;
    }

    public function login(array $credentials)
    {
        //$credentials = $request->only('email', 'password');

        $user = User::with('roles','company')->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        // حذف التوكنات القديمة إذا نريد توكن واحد فقط لكل مستخدم
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return $data=[
            'user' => $user,
            'token' => $token
        ];
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }


    public function logout(User $user, string $tokenId): void
    {
        $user->tokens()->where('id', $tokenId)->delete();
    }
}

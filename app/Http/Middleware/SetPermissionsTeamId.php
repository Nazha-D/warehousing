<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionsTeamId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if ($user = Auth::user()) {
                app(PermissionRegistrar::class)->setPermissionsTeamId($user->company_id);
            }

            return $next($request);
        }
        catch(\Exception $e)
        {
            \Log::error("Exception caught in SetPermissionsTeamId: " .$e->getMessage());
        }
    }
}

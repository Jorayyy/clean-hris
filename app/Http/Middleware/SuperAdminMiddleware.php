<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and is a Super Admin
        // This checks both the legacy 'role' column AND the Spatie role for safety
        if ($request->user() && ($request->user()->role === 'super-admin' || $request->user()->hasRole('Super Admin'))) {
            return $next($request);
        }

        // Return a 403 response if not a Super Admin
        abort(403, 'You do not have the authorization to access the Roles & Permissions management.');
    }
}

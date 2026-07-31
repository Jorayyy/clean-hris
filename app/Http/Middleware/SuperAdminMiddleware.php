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
        if ($request->user()) {
            $user = $request->user();
            
            if ($user->isSuperAdmin() || $user->hasAnyRole(['Accounting Admin', 'Accounting-Admin'])) {
                return $next($request);
            }
            abort(403, 'Unauthorized access.');
        }

        // Return a 403 response if not authorized
        abort(403, 'You do not have the authorization to access this section.');
    }
}

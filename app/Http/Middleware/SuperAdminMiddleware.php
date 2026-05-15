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
            $isAccounting = ($user->employee && ($user->employee->classification === 'Accounting' || $user->employee->level === 'Accounting')) || $user->hasRole('Accounting Admin');

            if ($user->is_super_admin || $isAccounting) {
                return $next($request);
            }
            abort(403, 'Unauthorized access.');
        }

        // Return a 403 response if not authorized
        abort(403, 'You do not have the authorization to access this section.');
    }
}

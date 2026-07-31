<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EmployeeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If they aren't logged in at all, send them to login safely
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        // 2. Perform the role check
        if (Auth::user()->isEmployee()) {
            return $next($request);
        }

        // 3. FIX: If they are logged in but don't have the right role, 
        // abort with a 403 Forbidden instead of redirecting to login.
        abort(403, 'Unauthorized access.');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateApiSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Allow unauthenticated access for login and logout routes
        if ($request->is('api/login') || $request->is('api/logout') || $request->is('api/debug-login')) {
            return $next($request);
        }

        // Everything else requires a valid session
        if (!Auth::guard('web')->check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}

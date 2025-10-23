<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     * These middleware run during every request.
     */
    protected $middleware = [
        // 🔹 Ensure proxies handled properly
        \App\Http\Middleware\TrustProxies::class,

        // 🔹 Custom CORS for token-based requests
        \App\Http\Middleware\CorsMiddleware::class,

        // 🔹 Default Laravel middlewares
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * Middleware groups for web and API routes.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // ✅ We do NOT include VerifyUserToken globally —
            // it’s route-specific in api.php (correct!)
        ],
    ];

    /**
     * Route middleware aliases (usable in routes or controllers).
     */
    protected $routeMiddleware = [
        // Default Laravel middleware
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // ✅ Hybrid Token Auth (Retailer/Admin APIs)
        'verify.user.token' => \App\Http\Middleware\VerifyUserToken::class,

        // ✅ Optional: Admin session-based routes (for backend dashboard)
        'auth.admin' => \App\Http\Middleware\AuthenticateAdmin::class,
    ];
}

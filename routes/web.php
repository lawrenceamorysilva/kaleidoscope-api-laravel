<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SSOLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DropshipOrderController;

use App\Http\Controllers\ShippingController;
use App\Http\Controllers\NetoProductController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Session-based auth is used; CSRF middleware is active by default.
|
*/





// All web routes (session + CSRF)
Route::middleware(['web'])->group(function () {

    // --- Authentication ---
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout']);

    // --- Authenticated user routes ---
    Route::middleware(['auth:web'])->group(function () {
        Route::get('/auth/me', [LoginController::class, 'me']);
       /* Route::post('/dropship-orders', [DropshipOrderController::class, 'store']);
        Route::put('/dropship-orders/{id}', [DropshipOrderController::class, 'update']);*/
        Route::get('/dropship-orders/openSummary', [DropshipOrderController::class, 'openSummary']);
        Route::get('/dropship-orders/history', [DropshipOrderController::class, 'history']);
        Route::get('/dropship-orders/{id}', [DropshipOrderController::class, 'show']);
        /*Route::post('/dropship-orders/bulkUpdate', [DropshipOrderController::class, 'bulkUpdateStatus']);*/
    });

    // Optional landing page
    Route::get('/', fn() => view('welcome'));

    // --- SSO login ---
    Route::get('/auth/sso_login', [SSOLoginController::class, 'handleSSO']);

    // --- Retailer-only routes ---
    Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);
    Route::get('/neto-products', [NetoProductController::class, 'index']);
    Route::get('/products/sku/{sku}', [NetoProductController::class, 'getBySku']);
    Route::post('/products/lookup', [NetoProductController::class, 'lookupSkus']);
});

Route::middleware(['web', 'auth:web', 'cors'])->group(function () {
    Route::post('/dropship-orders', [DropshipOrderController::class, 'store']);
    Route::put('/dropship-orders/{id}', [DropshipOrderController::class, 'update']);
    Route::post('/dropship-orders/bulkUpdate', [DropshipOrderController::class, 'bulkUpdateStatus']);
});





Route::get('/debug-session', function () {
    return [
        'session_id' => session()->getId(),
        'cookies' => request()->cookies->all(),
        'user' => Auth::guard('web')->user(),
    ];
});

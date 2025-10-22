<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SSOLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DropshipOrderController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\NetoProductController;

// Session-based web routes
Route::middleware(['web'])->group(function () {

    // Authentication
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout']);

    // Authenticated routes (GETs work)
    Route::middleware(['auth:web'])->group(function () {
        Route::get('/auth/me', [LoginController::class, 'me']);
        Route::get('/dropship-orders/openSummary', [DropshipOrderController::class, 'openSummary']);
        Route::get('/dropship-orders/history', [DropshipOrderController::class, 'history']);
        Route::get('/dropship-orders/{id}', [DropshipOrderController::class, 'show']);
    });

    // Retailer-only / public GET routes
    Route::get('/', fn() => view('welcome'));
    Route::get('/auth/sso_login', [SSOLoginController::class, 'handleSSO']);
    Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);
    Route::get('/neto-products', [NetoProductController::class, 'index']);
    Route::get('/products/sku/{sku}', [NetoProductController::class, 'getBySku']);
    Route::post('/products/lookup', [NetoProductController::class, 'lookupSkus']);

    // ✅ Session + Auth + CORS applied **before auth** for all POST/PUT
    Route::middleware(['cors'])->group(function () {
        Route::post('/dropship-orders', [DropshipOrderController::class, 'store']);
        Route::put('/dropship-orders/{id}', [DropshipOrderController::class, 'update']);
        Route::post('/dropship-orders/bulkUpdate', [DropshipOrderController::class, 'bulkUpdateStatus']);
    });

    // OPTIONS fallback for preflight requests
    Route::options('/dropship-orders/{any}', function() {
        return response()->noContent();
    })->where('any', '.*')->middleware('cors');

});

// Debug session
Route::get('/debug-session', function () {
    return [
        'session_id' => session()->getId(),
        'cookies' => request()->cookies->all(),
        'user' => Auth::guard('web')->user(),
    ];
});

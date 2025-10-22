<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\NetoProductController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DropshipOrderController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\GeneralSettingsController;


/*use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
])->group(function () {
    // These now run WITH Laravel sessions enabled
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout']);
});*/


/*
|--------------------------------------------------------------------------
| API Routes (Session / Guard-based auth)
|--------------------------------------------------------------------------
|
| All API routes now use session-based auth.
| Angular frontends must use cookie-based sessions (or call /login first).
|
*/

// ----------------------
// Public / Shared Routes
// ----------------------
/*Route::middleware(['web'])->group(function () {
    Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);
    Route::get('/neto-products', [NetoProductController::class, 'index']);
    Route::get('/products/sku/{sku}', [NetoProductController::class, 'getBySku']);
    Route::post('/products/lookup', [NetoProductController::class, 'lookupSkus']);
});*/

// api.php
Route::middleware(['auth:web', 'cors'])->group(function () {
    Route::match(['POST', 'OPTIONS'], '/dropship-orders', [DropshipOrderController::class, 'store']);
    Route::match(['PUT', 'OPTIONS'], '/dropship-orders/{id}', [DropshipOrderController::class, 'update']);
    Route::match(['POST', 'OPTIONS'], '/dropship-orders/bulkUpdate', [DropshipOrderController::class, 'bulkUpdateStatus']);
});





// ----------------------
// Optional Debug Route
// ----------------------
Route::post('/debug-login', function (Request $request) {
    \Log::info('Debug Login Attempt', [
        'input' => $request->all(),
        'normalized_email' => strtolower(trim($request->input('email'))),
    ]);

    return response()->json([
        'received_email' => $request->input('email'),
        'normalized_email' => strtolower(trim($request->input('email'))),
        'all_inputs' => $request->all(),
    ]);
});

// ----------------------
// Retailer Authentication
// ----------------------
/*Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);*/

// ----------------------
// Retailer Protected Routes
// ----------------------
/*Route::middleware(['auth:web'])->group(function () {
    Route::get('/auth/me', [LoginController::class, 'me']);

    Route::post('/dropship-orders', [DropshipOrderController::class, 'store']);
    Route::put('/dropship-orders/{id}', [DropshipOrderController::class, 'update']);
    Route::get('/dropship-orders/openSummary', [DropshipOrderController::class, 'openSummary']);
    Route::get('/dropship-orders/history', [DropshipOrderController::class, 'history']);
    Route::get('/dropship-orders/{id}', [DropshipOrderController::class, 'show']);
    Route::post('/dropship-orders/bulkUpdate', [DropshipOrderController::class, 'bulkUpdateStatus']);
});*/

// ----------------------
// Admin Authentication
// ----------------------
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::post('/logout', [AdminLoginController::class, 'logout']);

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/me', [AdminLoginController::class, 'me']);

        Route::get('/dropship-orders', [DropshipOrderController::class, 'adminIndex']);
        Route::get('/dropship-export-history', [DropshipOrderController::class, 'adminExportHistory']);
        Route::post('/export-dropship-orders', [DropshipOrderController::class, 'exportCsv']);

        Route::get('/general-settings', [GeneralSettingsController::class, 'index']);
        Route::post('/general-settings/save-all', [GeneralSettingsController::class, 'saveAll']);
        Route::put('/general-settings/settings', [GeneralSettingsController::class, 'updateSettings']);
        Route::get('/general-settings/content/{key}', [GeneralSettingsController::class, 'showContent']);
        Route::put('/general-settings/content/{key}', [GeneralSettingsController::class, 'updateContent']);
    });
});

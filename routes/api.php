<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RetailerAuthController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\NetoProductController;
use App\Http\Controllers\DropshipOrderController;
use App\Http\Controllers\Admin\GeneralSettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Unified Hybrid Token Auth (Retailer + Admin)
| - Public routes for login / SSO
| - Protected routes use VerifyUserToken middleware
|--------------------------------------------------------------------------
*/

// ----------------------
// Public / Auth Routes
// ----------------------
Route::prefix('auth')->group(function () {
    // Retailer SSO Login
    Route::post('/sso_login', [RetailerAuthController::class, 'ssoLogin']);


    // Admin Login
    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminAuthController::class, 'login']);
    });
});

// ----------------------
// Shared Public Endpoints
// ----------------------
Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);
Route::get('/neto-products', [NetoProductController::class, 'index']);
Route::post('/products/lookup', [NetoProductController::class, 'lookupSkus']);
Route::get('/products/sku/{sku}', [NetoProductController::class, 'getBySku']);

// ----------------------
// Retailer Protected Routes
// ----------------------
Route::middleware(['verify.user.token'])->prefix('retailer')->group(function () {
    Route::post('/dropship-orders', [DropshipOrderController::class, 'store']);
    Route::put('/dropship-orders/{id}', [DropshipOrderController::class, 'update']);
    Route::get('/dropship-orders/openSummary', [DropshipOrderController::class, 'openSummary']);
    Route::get('/dropship-orders/history', [DropshipOrderController::class, 'history']);
    Route::get('/dropship-orders/{id}', [DropshipOrderController::class, 'show']);
    Route::post('/dropship-orders/bulkUpdate', [DropshipOrderController::class, 'bulkUpdateStatus']);


    Route::get('/debug-token', function (\Illuminate\Http\Request $request) {
        \Log::info('🕵️‍♂️ Debug token route hit', [
            'Authorization_header' => $request->header('Authorization'),
            'bearer_token' => $request->bearerToken(),
            'all_headers' => $request->headers->all(),
            'user_id_input' => $request->input('user_id'),
        ]);

        return response()->json([
            'message' => 'Debug token route hit successfully',
            'bearer_token' => $request->bearerToken()
        ]);
    });

});

// ----------------------
// Admin Protected Routes
// ----------------------
Route::middleware(['verify.user.token'])->prefix('admin')->group(function () {
    Route::get('/me', [AdminAuthController::class, 'me']);
    Route::post('/logout', [AdminAuthController::class, 'logout']);

    // Dropship Management
    Route::get('/dropship-orders', [DropshipOrderController::class, 'adminIndex']);
    Route::get('/dropship-export-history', [DropshipOrderController::class, 'adminExportHistory']);
    Route::post('/export-dropship-orders', [DropshipOrderController::class, 'exportCsv']);

    // General Settings
    Route::get('/general-settings', [GeneralSettingsController::class, 'index']);
    Route::post('/general-settings/save-all', [GeneralSettingsController::class, 'saveAll']);
    Route::put('/general-settings/settings', [GeneralSettingsController::class, 'updateSettings']);
    Route::get('/general-settings/content/{key}', [GeneralSettingsController::class, 'showContent']);
    Route::put('/general-settings/content/{key}', [GeneralSettingsController::class, 'updateContent']);
});

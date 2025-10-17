<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Retailer controllers
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\NetoProductController;
use App\Http\Controllers\Auth\FallbackLoginController;
use App\Http\Controllers\DropshipOrderController;

// Admin controllers
use App\Http\Controllers\Admin\AdminAuthController;

// ----------------------
// Retailer Portal | Admin Portal routes
// ----------------------
Route::middleware('api')->group(function () {
    // Shared routes
    Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);
    Route::get('/neto-products', [NetoProductController::class, 'index']);

    // Retailer portal
    Route::post('/products/lookup', [NetoProductController::class, 'lookupSkus']);
    Route::get('/products/sku/{sku}', [NetoProductController::class, 'getBySku']);
    Route::post('/fallback_login', [FallbackLoginController::class, 'login']);
});

// Debug login (optional)
Route::post('/debug-fallback-login', function (Request $request) {
    \Log::info('iPad Debug Login Attempt', [
        'raw_input' => $request->all(),
        'raw_email' => $request->input('email'),
        'normalized_email' => strtolower(trim($request->input('email')))
    ]);

    return response()->json([
        'received_email' => $request->input('email'),
        'normalized_email' => strtolower(trim($request->input('email'))),
        'all_inputs' => $request->all()
    ]);
});

// Retailer login & auth (token-based)
Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('retailer')->plainTextToken;

    return response()->json(['token' => $token]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', function (Request $request) {
        return response()->json($request->user());
    });

    // Retailer dropship orders
    Route::post('/dropship-orders', [DropshipOrderController::class, 'store']);
    Route::put('/dropship-orders/{id}', [DropshipOrderController::class, 'update']);
    Route::get('/dropship-orders/openSummary', [DropshipOrderController::class, 'openSummary']);
    Route::get('/dropship-orders/history', [DropshipOrderController::class, 'history']);
    Route::get('/dropship-orders/{id}', [DropshipOrderController::class, 'show']);
    Route::post('/dropship-orders/bulkUpdate', [DropshipOrderController::class, 'bulkUpdateStatus']);
});

// ----------------------
// Admin Portal routes (session-based)
// ----------------------
Route::prefix('admin')->group(function () {
    // Admin login (session-based)
    Route::post('/login', [AdminAuthController::class, 'login']);

    // Protected admin routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('/me', [AdminAuthController::class, 'me']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        // Dropship routes
        Route::get('/dropship-orders', [DropshipOrderController::class, 'adminIndex']);
        Route::get('/dropship-export-history', [DropshipOrderController::class, 'adminExportHistory']);
        Route::post('/export-dropship-orders', [DropshipOrderController::class, 'exportCsv']);

        // General Settings routes
        Route::get('/general-settings', [\App\Http\Controllers\Admin\GeneralSettingsController::class, 'index']);
        Route::post('/general-settings/save-all', [\App\Http\Controllers\Admin\GeneralSettingsController::class, 'saveAll']);
        Route::put('/general-settings/settings', [\App\Http\Controllers\Admin\GeneralSettingsController::class, 'updateSettings']);
        Route::get('/general-settings/content/{key}', [\App\Http\Controllers\Admin\GeneralSettingsController::class, 'showContent']);
        Route::put('/general-settings/content/{key}', [\App\Http\Controllers\Admin\GeneralSettingsController::class, 'updateContent']);
    });
});

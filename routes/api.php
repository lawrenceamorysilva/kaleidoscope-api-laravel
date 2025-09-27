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
    //shared
    Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);
    Route::get('/neto-products', [NetoProductController::class, 'index']);

    //retailer portal
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

// Retailer login & auth
Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('retailer')->plainTextToken;

    return response()->json(['token' => $token]);
});

Route::middleware('auth:sanctum')->get('/auth/me', function (Request $request) {
    return response()->json($request->user());
});

// Retailer dropship orders
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/dropship-orders', [DropshipOrderController::class, 'store']);
    Route::put('/dropship-orders/{id}', [DropshipOrderController::class, 'update']);
    Route::get('/dropship-orders/openSummary', [DropshipOrderController::class, 'openSummary']);
    Route::get('/dropship-orders/history', [DropshipOrderController::class, 'history']);
    Route::get('/dropship-orders/{id}', [DropshipOrderController::class, 'show']);
    Route::post('/dropship-orders/bulkUpdate', [DropshipOrderController::class, 'bulkUpdateStatus']);
});

// ----------------------
// Admin Portal routes
// ----------------------
Route::prefix('admin')->group(function () {
    // Login (no auth yet)
    Route::post('/login', [AdminAuthController::class, 'login']);

    // Protected routes (auth:admin guard)
    Route::middleware('auth:admin')->group(function () {
        Route::get('/me', [AdminAuthController::class, 'me']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        Route::get('/dropship-orders', [DropshipOrderController::class, 'adminIndex']);
        Route::post('/export-dropship-orders', [DropshipOrderController::class, 'exportCsv']);

        // Example: future admin routes
        // Route::apiResource('/users', AdminUserController::class);
        // Route::apiResource('/orders', OrderController::class);
        // Route::apiResource('/faq', FaqController::class);
        // Route::apiResource('/terms', TermsController::class);
    });
});

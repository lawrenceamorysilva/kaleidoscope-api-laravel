<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\NetoProductController;
use App\Http\Controllers\Auth\FallbackLoginController;
use App\Http\Controllers\DropshipOrderController;

Route::middleware('api')->group(function () {
    Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);
    Route::get('/neto-products', [NetoProductController::class, 'index']);
    Route::post('/products/lookup', [NetoProductController::class, 'lookupSkus']);
    Route::get('/products/sku/{sku}', [NetoProductController::class, 'getBySku']);
    Route::post('/fallback_login', [FallbackLoginController::class, 'login']);
});

// 🔐 Login to get token
Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('retailer')->plainTextToken;

    return response()->json(['token' => $token]);
});

// 🔐 Get authenticated user
Route::middleware('auth:sanctum')->get('/auth/me', function (Request $request) {
    return response()->json($request->user());
});


/*Route::middleware('auth:sanctum')->put('/dropship-orders/{id}', [DropshipOrderController::class, 'update']);
Route::get('/dropship-orders/{id}', [DropshipOrderController::class, 'show']);

Route::get('/dropship-orders/{id}', [DropshipOrderController::class, 'show']);*/

// 🔐 Save dropship order
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/dropship-orders', [DropshipOrderController::class, 'store']);
    Route::put('/dropship-orders/{id}', [DropshipOrderController::class, 'update']);
    Route::get('/dropship-orders/{id}', [DropshipOrderController::class, 'show']);
});

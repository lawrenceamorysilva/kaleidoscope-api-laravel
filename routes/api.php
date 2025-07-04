<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\NetoProductController;
use App\Http\Controllers\Auth\FallbackLoginController;



Route::middleware('api')->group(function () {
    Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);

    // Add this line to expose Neto products to your Angular admin portal
    Route::get('/neto-products', [NetoProductController::class, 'index']);
});

Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('retailer')->plainTextToken;

    return response()->json(['token' => $token]);
});


Route::post('/fallback_login', [FallbackLoginController::class, 'login']);

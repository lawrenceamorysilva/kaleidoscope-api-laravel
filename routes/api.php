<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\NetoProductController;

Route::middleware('api')->group(function () {
    Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);

    // Add this line to expose Neto products to your Angular admin portal
    Route::get('/neto-products', [NetoProductController::class, 'index']);
});

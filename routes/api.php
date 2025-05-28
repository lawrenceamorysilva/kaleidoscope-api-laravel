<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ShippingController;

Route::middleware('api')->group(function () {
    Route::get('/shipping/cost', [ShippingController::class, 'getShippingCost']);
});
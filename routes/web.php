<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| These are browser routes, primarily for landing pages or redirect-based
| SSO flows. The actual API login logic happens under routes/api.php.
|--------------------------------------------------------------------------
*/

// Landing page (optional)
Route::get('/', function () {
    return view('welcome');
});

// Optional fallback or debug login (for browser/manual testing)
Route::post('/auth/fallback_login', [\App\Http\Controllers\Auth\FallbackLoginController::class, 'login']);

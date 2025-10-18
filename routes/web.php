<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SSOLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Only needed for SSO login or serving basic pages.
| All API calls now use JWT; no CSRF cookie required.
|
*/

// Landing page (optional)
Route::get('/', function () {
    return view('welcome');
});

// SSO login
Route::get('/auth/sso_login', [SSOLoginController::class, 'handleSSO']);

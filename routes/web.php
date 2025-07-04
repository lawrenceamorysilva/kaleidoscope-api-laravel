<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SSOLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/sso_login', [SSOLoginController::class, 'handleSSO']);


Route::post('/auth/fallback_login', [\App\Http\Controllers\Auth\FallbackLoginController::class, 'login']);

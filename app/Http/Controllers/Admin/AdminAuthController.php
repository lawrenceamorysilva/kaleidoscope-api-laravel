<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;
use App\Http\Controllers\Controller;


use Laravel\Sanctum\PersonalAccessToken;

class AdminAuthController extends Controller
{
    /** Login using Sanctum token */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();

        $user = Auth::guard('admin')->user();

        return response()->json([
            'user' => $user,
            'message' => 'Login successful',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(Auth::guard('admin')->user());
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }
}

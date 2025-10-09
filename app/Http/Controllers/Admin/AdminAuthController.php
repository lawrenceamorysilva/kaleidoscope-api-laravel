<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;
use App\Http\Controllers\Controller;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = AdminUser::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('AdminPortal')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        // Retrieve authenticated admin user via Sanctum
        $user = $request->user('admin');
        if (! $user) {
            return response()->json(null, 401);
        }

        // 🔒 Use the bearer token to create a token-specific microcache key
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['message' => 'Missing token'], 401);
        }

        $cacheKey = 'admin_me_' . md5($token);

        // ⚡ Return cached version if available
        if ($cached = cache()->get($cacheKey)) {
            return response()->json($cached);
        }

        // ✅ Minimal payload (avoid relationships or large data)
        $payload = [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role,
            'is_active' => $user->is_active,
        ];

        // 🕒 Cache per token for 30 seconds
        cache()->put($cacheKey, $payload, now()->addSeconds(30));

        return response()->json($payload);
    }



    public function logout(Request $request)
    {
        $request->user('admin')->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}

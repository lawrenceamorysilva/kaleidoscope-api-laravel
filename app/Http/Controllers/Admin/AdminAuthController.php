<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;
use App\Helpers\TokenHelper;

class AdminAuthController extends Controller
{
    /**
     * Handle Admin login (username + password)
     * Endpoint: POST /api/admin/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = strtolower(trim($request->input('email')));
        $password = $request->input('password');

        $admin = AdminUser::where('email', $email)->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            return response()->json(['message' => 'Invalid admin credentials'], 401);
        }

        // 🧩 Generate token for Admin Portal
        $token = TokenHelper::generate($admin->id, 'admin');

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $admin->id,
                'email' => $admin->email,
                'name' => $admin->name,
                'role' => $admin->role ?? 'admin',
            ],
        ]);
    }

    /**
     * Return token-based admin context
     */
    public function me(Request $request)
    {
        return response()->json([
            'user_id' => $request->get('user_id'),
            'portal' => $request->get('portal'),
            'token_expiry' => $request->get('token_expiry'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Helpers\TokenHelper;

class RetailerAuthController extends Controller
{
    /**
     * Handle Retailer SSO or manual login.
     * Endpoint: GET /api/auth/sso_login
     */
    public function ssoLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->input('email')));

        // Lookup user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid credentials: user not found'], 401);
        }

        // Generate token
        $token = TokenHelper::generate($user->id, 'retailer');

        // Return token + full user
        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }


    /**
     * Verify token (for “/auth/me”-like checks)
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

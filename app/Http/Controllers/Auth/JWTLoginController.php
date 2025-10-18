<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTLoginController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');

        // Step 1: check user locally
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error'=>'User not found'], 404);
        }

        // Step 2: optionally call Neto API to verify user
        // $netoValid = NetoApi::checkUser($email);
        // if (!$netoValid) return response()->json(['error'=>'Invalid Neto user'], 401);

        // Step 3: create a JWT token without checking password
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user
        ]);
    }



    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}

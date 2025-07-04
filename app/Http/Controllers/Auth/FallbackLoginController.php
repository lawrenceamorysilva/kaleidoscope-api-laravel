<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FallbackLoginController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');

        // Dummy auth like the images site — just match by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        Auth::login($user, true);

        $token = $user->createToken('retailer')->plainTextToken;

        return response()->json(['token' => $token]);
    }
}

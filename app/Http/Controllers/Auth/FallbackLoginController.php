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
        // Normalize email to handle iPad capitalization / whitespace
        $email = strtolower(trim($request->input('email')));
        $password = $request->input('password'); // captured but ignored

        // Find user case-insensitively
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Log in user (password ignored)
        Auth::login($user, true);

        // Create a Sanctum token
        $token = $user->createToken('retailer')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}

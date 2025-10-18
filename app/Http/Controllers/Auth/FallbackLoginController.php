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
        // Normalize email
        $email = strtolower(trim($request->input('email')));
        $password = $request->input('password');

        // Find user (case-insensitive)
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        // Log in user via session (SPA compatible)
        Auth::login($user, true);
        $request->session()->regenerate();

        // Return full user info
        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'customer_id' => $user->customer_id,
                'username' => $user->username,
                'on_credit_hold' => $user->on_credit_hold,
                'default_invoice_terms' => $user->default_invoice_terms,
                'bill_company' => $user->bill_company,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SSOLoginController extends Controller
{
    public function handleSSO(Request $request)
    {
        $username = $request->query('username');
        $email = $request->query('email');
        $signature = $request->query('signature');

        // Optional security: Validate HMAC signature
        $expectedSignature = hash_hmac('sha256', "{$username}|{$email}", env('SSO_SECRET_KEY'));
        if ($signature && !hash_equals($signature, $expectedSignature)) {
            abort(403, 'Invalid signature');
        }

        // Auto-register if user doesn't exist
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $username,
                'email' => $email,
                'password' => Hash::make(Str::random(16)),// random password
            ]);
        }

        // Log in the user (creates Laravel session)
        Auth::login($user, true);

        // Generate JWT (or session cookie), then redirect to Angular
        $token = $user->createToken('retailer')->plainTextToken;

        //return redirect()->to("https://retailer.kaleidoscope.com.au/auth/callback?token={$token}");
        return redirect()->to("http://retailer.localhost:4200/auth/callback?token={$token}");

    }

    public function fallbackLogin(Request $request)
    {
        $email = $request->input('email');

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Retailer',
                'email' => $email,
                'password' => Hash::make(str()->random(16)), // random password
            ]);
        }

        $token = $user->createToken('retailer')->plainTextToken;
        return response()->json(['token' => $token]);
    }
}


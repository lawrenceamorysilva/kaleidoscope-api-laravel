<?php
//NOTICE: this was JWT before now its Laravel Classic Session Auth I'm just a slacked not to rename the controller name

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Log in a retailer user via email.
     */


    public function login(Request $request)
    {
        $email = $request->input('email');

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        // Log the user in manually
        Auth::guard('web')->login($user);
        $request->session()->save();
        \Log::info('Login successful', ['user' => $user->id, 'session' => session()->getId()]);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
        ]);
    }


    /**
     * Get the currently authenticated retailer user.
     */
    public function me()
    {


        $user = Auth::guard('web')->user();


        \Log::info('ETITSSSSSSS Authenticated user:', ['user' => $user]);

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json($user);
    }

    /**
     * Log out the current retailer user.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // ✅ session auth
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }


}

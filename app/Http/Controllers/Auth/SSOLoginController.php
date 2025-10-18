<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NetoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class SSOLoginController extends Controller
{
    protected $neto;

    public function __construct(NetoService $neto)
    {
        $this->neto = $neto;
    }

    /**
     * Handle SSO login from link
     */
    public function handleSSO(Request $request)
    {
        $username = $request->query('username');
        $email = $request->query('email');
        $signature = $request->query('signature');

        \Log::info('SSO handleSSO called', compact('username', 'email', 'signature'));

        // Determine frontend base URL
        $baseUrl = match (config('app.env')) {
            'local' => 'http://retailer.localhost:4200',
            'staging' => 'https://staging-retailer.kaleidoscope.com.au',
            default => 'https://retailer.kaleidoscope.com.au',
        };

        // Validate optional HMAC signature
        if ($signature) {
            $expectedSignature = hash_hmac('sha256', "{$username}|{$email}", env('SSO_SECRET_KEY'));
            if (!hash_equals($signature, $expectedSignature)) {
                \Log::warning('SSO invalid signature', compact('username', 'email'));
                $errorMessage = urlencode('Invalid SSO signature');
                return redirect()->to("{$baseUrl}/login?error={$errorMessage}");
            }
        }

        // Fetch latest customer data from Neto
        $customer = $this->neto->getCustomerByEmail($email);
        \Log::info('Neto Customer API response', ['request_email' => $email, 'response_body' => json_encode($customer)]);

        if (!$customer) {
            \Log::warning('Neto customer not found', compact('email', 'username'));
            $errorMessage = urlencode('Customer not found in Neto');
            return redirect()->to("{$baseUrl}/login?error={$errorMessage}");
        }

        $customer = $customer['Customer'][0] ?? null;
        \Log::info('Neto customer fetched', compact('customer'));

        // Prepare user data
        $data = [
            'name' => $customer['Username'] ?? $username,
            'customer_id' => $customer['ID'] ?? null,
            'username' => $customer['Username'] ?? null,
            'on_credit_hold' => ($customer['OnCreditHold'] ?? 'False') === 'True',
            'default_invoice_terms' => $customer['DefaultInvoiceTerms'] ?? null,
            'bill_company' => $customer['BillingAddress']['BillCompany'] ?? null,
        ];

        // Create or update the user
        $user = User::updateOrCreate(
            ['email' => $email],
            array_merge($data, [
                'password' => Hash::make(Str::random(16)), // only for new user
            ])
        );
        \Log::info('User created or updated', ['user_id' => $user->id]);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);
        \Log::info('JWT token generated', ['token' => $token]);

        // Log the user in for session (optional)
        Auth::login($user, true);

        // Redirect to frontend home page with token
        \Log::info('Redirecting to frontend', ['url' => "{$baseUrl}/?token={$token}"]);
        return redirect()->to("{$baseUrl}/?token={$token}");
    }

    /**
     * Fallback login for just email
     */
    public function fallbackLogin(Request $request)
    {
        $email = $request->input('email');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Retailer',
                'password' => Hash::make(Str::random(16)),
            ]
        );

        $token = JWTAuth::fromUser($user);

        return response()->json(['token' => $token]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NetoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SSOLoginController extends Controller
{
    protected $neto;

    public function __construct(NetoService $neto)
    {
        $this->neto = $neto;
    }

    public function handleSSO(Request $request)
    {
        $username  = $request->query('username');
        $email     = $request->query('email');
        $signature = $request->query('signature');

        Log::info('SSO handleSSO called', compact('username', 'email', 'signature'));

        // Determine base URL based on environment (compatible with PHP 7.4)
        switch (config('app.env')) {
            case 'local':
                $baseUrl = 'http://retailer.localhost:4200';
                break;
            case 'staging':
                $baseUrl = 'https://staging-retailer.kaleidoscope.com.au';
                break;
            default:
                //$baseUrl = 'https://retailer.kaleidoscope.com.au';
                $baseUrl = 'https://dropshipping.kaleidoscope.com.au';
                break;
        }

        // --- Verify SSO signature if provided ---
        if ($signature) {
            $expectedSignature = hash_hmac('sha256', "{$username}|{$email}", env('SSO_SECRET_KEY'));
            if (!hash_equals($signature, $expectedSignature)) {
                $errorMessage = urlencode('Invalid SSO signature');
                return redirect()->to("{$baseUrl}/login?error={$errorMessage}");
            }
        }

        // --- Look up the customer in Neto ---
        $customer = $this->neto->getCustomerByEmail($email);
        if (!$customer) {
            $errorMessage = urlencode('Customer not found in Neto');
            return redirect()->to("{$baseUrl}/login?error={$errorMessage}");
        }

        $customer = $customer['Customer'][0] ?? null;

        $data = [
            'name'                 => $customer['Username'] ?? $username,
            'customer_id'          => $customer['ID'] ?? null,
            'username'             => $customer['Username'] ?? null,
            'on_credit_hold'       => ($customer['OnCreditHold'] ?? 'False') === 'True',
            'default_invoice_terms'=> $customer['DefaultInvoiceTerms'] ?? null,
            'bill_company'         => $customer['BillingAddress']['BillCompany'] ?? null,
        ];

        // --- Create or update user locally ---
        $user = User::updateOrCreate(
            ['email' => $email],
            array_merge($data, [
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)), // only for new user
            ])
        );

        // --- Session-based login (use web guard, not api) ---
        \Illuminate\Support\Facades\Auth::guard('web')->login($user, true);

        Log::info('SSO Login success', [
            'user_id' => $user->id,
            'session_id' => session()->getId(),
        ]);

        return redirect()->to($baseUrl);
    }

}

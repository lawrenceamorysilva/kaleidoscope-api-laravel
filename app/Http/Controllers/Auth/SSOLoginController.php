<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NetoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SSOLoginController extends Controller
{
    protected $neto;

    public function __construct(NetoService $neto)
    {
        $this->neto = $neto;
    }

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

        // Fetch from Neto (always fetch to sync latest info)
        $customer = $this->neto->getCustomerByEmail($email);

        if (!$customer) {
            return response()->json(['error' => 'Neto customer not found'], 404);
        }

        // Merge update/create fields
        $data = [
            'name' => $customer['Username'] ?? $username,
            'customer_id' => $customer['ID'] ?? null,
            'username' => $customer['Username'] ?? null,
            'on_credit_hold' => $customer['OnCreditHold'] === 'True',
            'default_invoice_terms' => $customer['DefaultInvoiceTerms'] ?? null,
            'bill_company' => $customer['BillingAddress']['BillCompany'] ?? null,
        ];

        $user = User::updateOrCreate(
            ['email' => $email],
            array_merge($data, [
                'password' => Hash::make(Str::random(16)) // only used if new
            ])
        );

        Auth::login($user, true);
        $token = $user->createToken('retailer')->plainTextToken;

        $env = config('app.env');

        switch ($env) {
            case 'local':
                $baseUrl = 'http://retailer.localhost:4200';
                break;
            case 'staging':
                $baseUrl = 'https://staging-retailer.kaleidoscope.com.au';
                break;
            default:
                $baseUrl = 'https://retailer.kaleidoscope.com.au';
                break;
        }


        return redirect()->to("{$baseUrl}/auth/callback?token={$token}");
    }


    public function fallbackLogin(Request $request)
    {
        $email = $request->input('email');

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Retailer',
                'email' => $email,
                'password' => Hash::make(str()->random(16)),
            ]);
        }

        $token = $user->createToken('retailer')->plainTextToken;
        return response()->json(['token' => $token]);
    }
}

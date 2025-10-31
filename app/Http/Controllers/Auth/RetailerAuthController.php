<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Helpers\TokenHelper;
use App\Services\NetoService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RetailerAuthController extends Controller
{
    protected $neto;

    public function __construct(NetoService $neto)
    {
        $this->neto = $neto;
    }

    /**
     * Handle Retailer SSO or manual login.
     * Endpoint: POST /api/auth/sso_login
     */
    public function ssoLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email     = strtolower(trim($request->input('email')));
        $username  = $request->input('username') ?? null;
        $signature = $request->input('signature') ?? null;

        // --- Optional SSO signature verification ---
        /*
        if ($signature && $username) {
            $expectedSignature = hash_hmac('sha256', "{$username}|{$email}", env('SSO_SECRET_KEY'));
            if (!hash_equals($signature, $expectedSignature)) {
                return response()->json(['message' => 'Invalid SSO signature'], 401);
            }
        }
        */

        // --- Check Neto for user ---
        $customerResponse = $this->neto->getCustomerByEmail($email);

        /*Log::info('TAE after', ['user_id' => $customerResponse]);*/

        if (!$customerResponse) {
            return response()->json([
                'message' => 'Customer not found in Neto. Please contact support.'
            ], 401);
        }

        $customer = $customerResponse;

        $data = [
            'name'                  => trim(($customer['BillingAddress']['BillFirstName'] ?? '') . ' ' . ($customer['BillingAddress']['BillLastName'] ?? '')) ?: ($customer['Username'] ?? ''),
            'customer_id'           => $customer['ID'],
            'username'              => $customer['Username'],
            'on_credit_hold'        => ($customer['OnCreditHold'] ?? 'False') === 'True',
            'default_invoice_terms' => $customer['DefaultInvoiceTerms'],
            'bill_company'          => $customer['BillingAddress']['BillCompany'],
        ];

        // --- Create or update user locally ---
        $user = User::updateOrCreate(
            ['email' => $email],
            array_merge($data, [
                'password' => Hash::make(Str::random(16)), // Only for new users
            ])
        );

        // --- Generate API token ---
        $tokenData = TokenHelper::generate($user->id, 'retailer');

        // --- Load Portal Settings + Contents ---
        $settings = \App\Models\PortalSetting::select('key', 'value', 'type')
            ->get()
            ->pluck('value', 'key');

        $contents = \App\Models\PortalContent::select('key', 'title', 'content')
            ->get()
            ->keyBy('key');


        /*Log::info('user info:', ['user' => $user]);*/
        //Log::info('CONTENTS & SETTINGS after', ['contents' => $contents, 'settings',$settings]);

        // --- Return token + user + config ---
        return response()->json([
            'token'      => $tokenData['token'],
            'expires_at' => $tokenData['expires_at'],
            'user'       => $user,
            'settings'   => $settings,
            'contents'   => $contents,
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

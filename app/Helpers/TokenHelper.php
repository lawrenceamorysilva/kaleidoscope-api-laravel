<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class TokenHelper
{
    /**
     * Generate a baked hybrid token and store it in user_tokens table.
     *
     * @param  int    $userId
     * @param  string $portal  'retailer' | 'admin'
     * @return array[]
     */
    public static function generate($userId, $portal)
    {
        $secretKey = Config::get('token.secret_key', env('TOKEN_SECRET_KEY'));
        $expiryHours = (int) Config::get('token.expiry_hours', env('TOKEN_EXPIRY_HOURS', 24));

        // Random unique token core
        $randomHex = bin2hex(random_bytes(16));

        // Compute expiry timestamp (UTC)
        $expiry = Carbon::now('UTC')->addHours($expiryHours);
        $encodedExpiry = base64_encode($expiry->timestamp);

        // Create HMAC signature (binds userId + portal + randomHex + expiry)
        $hmac = hash_hmac('sha256', "{$userId}|{$portal}|{$randomHex}|{$encodedExpiry}", $secretKey);

        // Final token format: userId.portal.randomHex.expiry.hmac
        $finalToken = "{$userId}.{$portal}.{$randomHex}.{$encodedExpiry}.{$hmac}";

        // Store in DB for verification/audit
        DB::table('user_tokens')->insert([
            'user_id'    => $userId,
            'token'      => $finalToken,
            'portal'     => $portal,
            'expires_at' => $expiry->toDateTimeString(),
            'created_at' => Carbon::now('UTC')->toDateTimeString(),
        ]);

        return [
            'token' => $finalToken,
            'expires_at' => $expiry->toIso8601String()
        ];
    }

    /**
     * Validate a baked hybrid token.
     *
     * @param  string $token
     * @return array{valid: bool, reason: string|null, data: array|null}
     */
    public static function validate($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 5) {
            return ['valid' => false, 'reason' => 'malformed', 'data' => null];
        }

        [$userId, $portal, $randomHex, $encodedExpiry, $hmac] = $parts;
        $secretKey = Config::get('token.secret_key', env('TOKEN_SECRET_KEY'));

        // Step 1: Verify HMAC integrity
        $checkHmac = hash_hmac('sha256', "{$userId}|{$portal}|{$randomHex}|{$encodedExpiry}", $secretKey);
        if (!hash_equals($checkHmac, $hmac)) {
            return ['valid' => false, 'reason' => 'invalid_hmac', 'data' => null];
        }

        // Step 2: Check expiry
        $expiryTimestamp = (int) base64_decode($encodedExpiry);
        if (time() > $expiryTimestamp) {
            return ['valid' => false, 'reason' => 'expired', 'data' => null];
        }

        // Step 3: Ensure token still exists in DB
        $exists = DB::table('user_tokens')->where('token', $token)->exists();
        if (!$exists) {
            return ['valid' => false, 'reason' => 'not_found', 'data' => null];
        }

        // ✅ Valid
        return [
            'valid' => true,
            'reason' => null,
            'data' => [
                'user_id' => (int) $userId,
                'portal'  => $portal,
                'expires_at' => Carbon::createFromTimestamp($expiryTimestamp)->toDateTimeString(),
            ],
        ];
    }
}

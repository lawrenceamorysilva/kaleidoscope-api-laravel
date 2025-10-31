<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\TokenHelper;

class VerifyUserToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        $source = 'none';

        // 🪵 Log entry (staging only)
        if (app()->environment('staging')) {
            logger()->debug('[VerifyUserToken] Entry', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);
        }

        // 1️⃣ Check query param
        if (!$authHeader) {
            $tokenFromQuery = $request->query('api_token');
            if ($tokenFromQuery) {
                $authHeader = 'Bearer ' . $tokenFromQuery;
                $source = 'query';
            }
        }

        // 2️⃣ Check body param (JSON or FormData)
        if (!$authHeader && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $tokenFromBody = $request->input('api_token');

            if (!$tokenFromBody) {
                $decoded = json_decode($request->getContent(), true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['api_token'])) {
                    $tokenFromBody = $decoded['api_token'];
                }
            }

            if ($tokenFromBody) {
                $authHeader = 'Bearer ' . $tokenFromBody;
                $source = 'body';
            }
        }

        // 3️⃣ Default to header
        if ($authHeader && $source === 'none') {
            $source = 'header';
        }

        // 4️⃣ Validate presence
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            logger()->warning("🚫 Unauthorized — missing bearer token", [
                'source' => $source,
                'url' => $request->fullUrl(),
            ]);
            return response()->json(['message' => 'Unauthorized: missing bearer token'], 401);
        }

        $token = trim($matches[1]);

        // 5️⃣ Validate token
        $result = TokenHelper::validate($token);
        if (!$result['valid']) {
            logger()->warning("🚫 Unauthorized — invalid token", [
                'source' => $source,
                'reason' => $result['reason'],
                'url' => $request->fullUrl(),
            ]);
            return response()->json(['message' => 'Unauthorized: ' . $result['reason']], 401);
        }

        // 6️⃣ Merge token context
        $portal = $result['data']['portal'] ?? 'unknown';
        $userId = $result['data']['user_id'] ?? null;

        $request->merge([
            'user_id' => $userId,
            'portal' => $portal,
            'token_expiry' => $result['data']['expires_at'] ?? null,
        ]);

        // ✅ Success log (info-level)
        /*logger()->info("[{$portal}] ✅ Token validated", [
            'user_id' => $userId,
            'source' => $source,
        ]);*/

        return $next($request);
    }
}

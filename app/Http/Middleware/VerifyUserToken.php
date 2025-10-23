<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\TokenHelper;

class VerifyUserToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        $source = 'none';

        // 🪵 Debug log for staging token issues
        logger()->debug('🔍 VerifyUserToken Entry', [
            'method' => $request->method(),
            'full_url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'has_body' => !empty($request->all()),
            'body' => $request->all(),
        ]);

        // 🔹 Fallback: query param (GET)
        if (!$authHeader) {
            $tokenFromQuery = $request->query('api_token');
            if ($tokenFromQuery) {
                $authHeader = 'Bearer ' . $tokenFromQuery;
                $source = 'query';
            }
        }

        // 🔹 Fallback: body param for POST/PUT/PATCH/DELETE
        if (!$authHeader && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $tokenFromBody = $request->input('api_token');

            // If still missing, try to parse raw JSON body
            if (!$tokenFromBody) {
                $rawContent = $request->getContent();
                if ($rawContent) {
                    $decoded = json_decode($rawContent, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['api_token'])) {
                        $tokenFromBody = $decoded['api_token'];
                    }
                }
            }

            if ($tokenFromBody) {
                $authHeader = 'Bearer ' . $tokenFromBody;
                $source = 'body';
            }
        }

        // 🔹 Header source
        if ($authHeader && $source === 'none') {
            $source = 'header';
        }

        // ✅ Must start with Bearer
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            logger()->warning("🚫 Unauthorized request — missing bearer token. Source: {$source}", [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);
            return response()->json(['message' => 'Unauthorized: missing bearer token'], 401);
        }

        $token = trim($matches[1]);

        // ✅ Validate token
        $result = TokenHelper::validate($token);
        if (!$result['valid']) {
            logger()->warning("🚫 Unauthorized request — invalid token. Source: {$source}, Reason: {$result['reason']}", [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);
            return response()->json([
                'message' => 'Unauthorized: ' . $result['reason']
            ], 401);
        }

        // ✅ Extract portal type (admin or retailer)
        $portal = $result['data']['portal'] ?? 'unknown';
        $userId = $result['data']['user_id'] ?? 'N/A';

        // ✅ Merge token context (user_id + portal + expiry)
        $request->merge([
            'user_id'      => $userId,
            'portal'       => $portal,
            'token_expiry' => $result['data']['expires_at'],
        ]);

        // 🧩 Enhanced portal-aware log
        logger()->info("[{$portal}] ✅ Token validated successfully. Source: {$source}, User ID: {$userId}");

        // ✅ Continue request
        return $next($request);
    }
}

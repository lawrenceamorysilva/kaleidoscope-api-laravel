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

        // ✅ Must start with Bearer
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return response()->json(['message' => 'Unauthorized: missing bearer token'], 401);
        }

        $token = trim($matches[1]);

        // ✅ Validate token
        $result = TokenHelper::validate($token);
        if (!$result['valid']) {
            return response()->json([
                'message' => 'Unauthorized: ' . $result['reason']
            ], 401);
        }

        // ✅ Merge token context (user_id + portal + expiry)
        $request->merge([
            'user_id' => $result['data']['user_id'],
            'portal'  => $result['data']['portal'],
            'token_expiry' => $result['data']['expires_at'],
        ]);

        // ✅ Continue request
        return $next($request);
    }
}

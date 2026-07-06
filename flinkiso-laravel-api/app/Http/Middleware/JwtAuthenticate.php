<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the Bearer JWT issued by the auth bridge and attaches the
 * decoded claims to the request as `flink_user`.
 */
class JwtAuthenticate
{
    public function __construct(private JwtService $jwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Missing bearer token'], 401);
        }

        $claims = $this->jwt->verify($token);
        if ($claims === null) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        $request->attributes->set('flink_user', $claims);

        return $next($request);
    }
}

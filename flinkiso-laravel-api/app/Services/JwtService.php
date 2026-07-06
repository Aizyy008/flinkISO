<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Minimal JWT issue/verify service for the CakePHP <-> Laravel auth bridge.
 */
class JwtService
{
    private string $secret;
    private int $ttlMinutes;

    public function __construct()
    {
        $this->secret = (string) config('flinkiso.jwt_secret');
        $this->ttlMinutes = (int) config('flinkiso.jwt_ttl_minutes');
    }

    /** Issue a signed JWT for a legacy FlinkISO user. */
    public function issue(array $claims): string
    {
        $now = time();
        $payload = array_merge([
            'iss' => 'flinkiso-laravel-api',
            'iat' => $now,
            'exp' => $now + ($this->ttlMinutes * 60),
        ], $claims);

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /** Verify a token and return its claims, or null if invalid/expired. */
    public function verify(string $token): ?array
    {
        try {
            return (array) JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (\Throwable $e) {
            return null;
        }
    }
}

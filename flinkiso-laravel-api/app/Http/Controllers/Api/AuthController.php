<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlinkUser;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Auth bridge: authenticates against the legacy CakePHP `users` table and
 * issues a JWT the new Laravel modules (and future services) can trust.
 */
class AuthController extends Controller
{
    public function __construct(private JwtService $jwt) {}

    /** POST /api/auth/login  { username, password } */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = FlinkUser::where('username', $data['username'])
            ->where('soft_delete', 0)
            ->first();

        if (!$user || !$user->verifyPassword($data['password'])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ((int) $user->status !== 1) {
            return response()->json(['message' => 'Account inactive'], 403);
        }

        $token = $this->jwt->issue([
            'sub' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'company_id' => $user->company_id,
            'is_approver' => (bool) $user->is_approver,
        ]);

        return response()->json([
            'token_type' => 'Bearer',
            'expires_in_minutes' => config('flinkiso.jwt_ttl_minutes'),
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
            ],
        ]);
    }

    /** GET /api/me  (protected) — returns the authenticated legacy user. */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->attributes->get('flink_user')]);
    }
}

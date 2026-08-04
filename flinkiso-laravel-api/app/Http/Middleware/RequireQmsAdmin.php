<?php

namespace App\Http\Middleware;

use App\Services\Qms\UserRoleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side authorization for administrative screens (Users & Roles, Workflow
 * rules). Rejects any authenticated user who is not a QMS administrator — for
 * both page views (GET) and actions (POST) — so access cannot be gained by
 * hitting the URL or posting directly. Hiding the menu items is only cosmetic;
 * this is the actual guard.
 */
class RequireQmsAdmin
{
    public function __construct(private UserRoleService $roles) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('flink_user');
        if (! $user) {
            return redirect('/login');
        }
        abort_unless($this->roles->isAdmin($user['id']), 403, 'Administrator access is required for this area.');

        return $next($request);
    }
}

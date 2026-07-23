<?php

namespace App\Services\Qms;

use App\Models\Qms\UserRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Resolves QMS document-workflow roles for legacy users. Roles live in
 * qms_user_roles (our DB); the legacy users table is only read. On first use we
 * seed a role row for every active legacy user from its native flags
 * (is_creator / is_approver / is_publisher; reviewer ← is_mr or is_hod).
 */
class UserRoleService
{
    /** All active legacy users (read-only). */
    public function legacyUsers(): Collection
    {
        return DB::connection('flinkiso')->table('users')
            ->where('soft_delete', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'is_creator', 'is_approver', 'is_publisher', 'is_mr', 'is_hod']);
    }

    /** Ensure every active legacy user has a qms_user_roles row (idempotent). */
    public function ensureSeeded(): void
    {
        $existing = UserRole::pluck('user_id')->flip();
        foreach ($this->legacyUsers() as $u) {
            if ($existing->has($u->id)) {
                continue;
            }
            UserRole::create([
                'user_id' => $u->id,
                'is_creator' => (bool) ($u->is_creator ?? 1),
                'is_reviewer' => (bool) (($u->is_mr ?? 0) || ($u->is_hod ?? 0)),
                'is_approver' => (bool) ($u->is_approver ?? 0),
                'is_publisher' => (bool) ($u->is_publisher ?? 0),
            ]);
        }
    }

    /** Legacy users joined with their QMS roles, for the management screen. */
    public function usersWithRoles(): Collection
    {
        $this->ensureSeeded();
        $roles = UserRole::get()->keyBy('user_id');
        return $this->legacyUsers()->map(function ($u) use ($roles) {
            $r = $roles->get($u->id);
            $u->roles = [
                'creator' => (bool) ($r?->is_creator),
                'reviewer' => (bool) ($r?->is_reviewer),
                'approver' => (bool) ($r?->is_approver),
                'publisher' => (bool) ($r?->is_publisher),
            ];
            return $u;
        });
    }

    /** Users holding a given role (role: creator|reviewer|approver|publisher). */
    public function usersWithRole(string $role): Collection
    {
        $this->ensureSeeded();
        $col = 'is_' . $role;
        $ids = UserRole::where($col, true)->pluck('user_id')->flip();
        return $this->legacyUsers()->filter(fn ($u) => $ids->has($u->id))->values();
    }

    /** Does a specific user hold a role? */
    public function has(string $userId, string $role): bool
    {
        $r = UserRole::where('user_id', $userId)->first();
        return (bool) ($r?->{'is_' . $role} ?? false);
    }

    /** Replace a user's roles from a management-screen submission. */
    public function setRoles(string $userId, array $roles): void
    {
        UserRole::updateOrCreate(['user_id' => $userId], [
            'is_creator' => (bool) ($roles['creator'] ?? false),
            'is_reviewer' => (bool) ($roles['reviewer'] ?? false),
            'is_approver' => (bool) ($roles['approver'] ?? false),
            'is_publisher' => (bool) ($roles['publisher'] ?? false),
        ]);
    }
}

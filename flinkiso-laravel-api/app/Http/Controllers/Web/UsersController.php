<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\UserRoleService;
use Illuminate\Http\Request;

/**
 * Users & Roles — assign the QMS document-workflow roles (Creator, Reviewer,
 * Approver, Publisher) to the FlinkISO users. Roles are stored in our database;
 * the legacy users table is never modified. This is what feeds the role-filtered
 * Reviewer/Approver/Publisher pickers on the document form.
 */
class UsersController extends Controller
{
    public function __construct(private UserRoleService $roles, private AuditTrailService $audit) {}

    public function index()
    {
        return view('users.index', ['users' => $this->roles->usersWithRoles()]);
    }

    public function update(Request $request, string $userId)
    {
        $data = $request->validate([
            'creator' => 'nullable|boolean',
            'reviewer' => 'nullable|boolean',
            'approver' => 'nullable|boolean',
            'publisher' => 'nullable|boolean',
        ]);
        $this->roles->setRoles($userId, [
            'creator' => $request->boolean('creator'),
            'reviewer' => $request->boolean('reviewer'),
            'approver' => $request->boolean('approver'),
            'publisher' => $request->boolean('publisher'),
        ]);
        $u = $request->session()->get('flink_user');
        $this->audit->record('qms_user_role', $userId, 'roles_updated', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['roles' => array_keys(array_filter([
                'creator' => $request->boolean('creator'), 'reviewer' => $request->boolean('reviewer'),
                'approver' => $request->boolean('approver'), 'publisher' => $request->boolean('publisher'),
            ]))],
        ]);
        return back()->with('ok', 'Roles updated.');
    }
}

<?php

namespace Database\Seeders;

use App\Services\Qms\UserRoleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One-time TEST-ACCOUNT provisioning for the document-control acceptance test.
 *
 * Creates four login accounts on the FlinkISO users table — a Creator, Reviewer,
 * Approver and Publisher — and assigns their QMS document-workflow roles. Each
 * new row is cloned from an existing user so every NOT-NULL column is satisfied,
 * then username / name / password / role flags are overridden. Idempotent:
 * accounts that already exist are skipped. Password for all: Test@123
 *
 * This is a provisioning seeder (run once), NOT application runtime writes.
 */
class TestWorkflowUsersSeeder extends Seeder
{
    private const PASSWORD = 'Test@123';

    public function run(): void
    {
        $salt = config('flinkiso.security_salt');
        $template = (array) DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->first();
        if (! $template) {
            $this->command->error('No existing FlinkISO user to clone as a template — cannot provision test accounts.');
            return;
        }

        $accounts = [
            ['username' => 'qms.creator',   'name' => 'QMS Creator (test)',   'flags' => ['is_creator' => 1]],
            ['username' => 'qms.reviewer',  'name' => 'QMS Reviewer (test)',  'flags' => ['is_creator' => 1, 'is_mr' => 1]],
            ['username' => 'qms.approver',  'name' => 'QMS Approver (test)',  'flags' => ['is_creator' => 1, 'is_approver' => 1]],
            ['username' => 'qms.publisher', 'name' => 'QMS Publisher (test)', 'flags' => ['is_creator' => 1, 'is_publisher' => 1]],
        ];
        $roleMap = [
            'qms.creator'   => ['creator' => true],
            'qms.reviewer'  => ['creator' => true, 'reviewer' => true],
            'qms.approver'  => ['creator' => true, 'approver' => true],
            'qms.publisher' => ['creator' => true, 'publisher' => true],
        ];

        $roleService = app(UserRoleService::class);

        foreach ($accounts as $a) {
            $existing = DB::connection('flinkiso')->table('users')->where('username', $a['username'])->first();
            if ($existing) {
                $id = $existing->id;
                $this->command->info("Skipped existing account: {$a['username']}");
            } else {
                $id = (string) Str::uuid();
                $row = $template;
                // Reset every role/identity flag, then apply this account's flags.
                foreach (['is_mr', 'is_mt', 'is_hod', 'is_view_all', 'is_approver', 'is_creator', 'is_publisher'] as $f) {
                    if (array_key_exists($f, $row)) {
                        $row[$f] = 0;
                    }
                }
                $row = array_merge($row, [
                    'id' => $id,
                    'name' => $a['name'],
                    'username' => $a['username'],
                    'password' => md5($salt . self::PASSWORD),
                    'soft_delete' => 0,
                    'status' => 1,
                ], $a['flags']);
                // Drop auto/duplicate keys that must be unique or auto-assigned.
                unset($row['sr_no'], $row['employee_id']);
                DB::connection('flinkiso')->table('users')->insert($row);
                $this->command->info("Created account: {$a['username']} / " . self::PASSWORD);
            }
            $roleService->setRoles($id, $roleMap[$a['username']]);
        }

        $this->command->info('Test accounts ready. All passwords: ' . self::PASSWORD);
    }
}

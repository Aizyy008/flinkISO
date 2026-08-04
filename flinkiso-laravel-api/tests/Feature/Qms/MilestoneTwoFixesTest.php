<?php

namespace Tests\Feature\Qms;

use App\Http\Controllers\Web\EvidenceController;
use App\Http\Middleware\RequireQmsAdmin;
use App\Models\Qms\Capa;
use App\Models\Qms\Evidence;
use App\Models\Qms\Incident;
use App\Models\Qms\Notification;
use App\Models\Qms\Task;
use App\Models\Qms\Workflow;
use App\Services\Qms\UserRoleService;
use App\Services\Qms\WorkflowEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Milestone 1.2 acceptance-fix checks: server-side admin authorization, real
 * tasks from workflow actions, evidence-download filename, and due/upcoming
 * reminders. Shares one in-memory sqlite across both DB connections.
 */
class MilestoneTwoFixesTest extends TestCase
{
    use RefreshDatabase;

    private const SALT = 'testsalt';
    private array $u = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['flinkiso.security_salt' => self::SALT]);
        config(['database.connections.flinkiso' => config('database.connections.' . config('database.default'))]);
        DB::purge('flinkiso');
        DB::connection('flinkiso')->setPdo(DB::connection()->getPdo());

        Schema::connection('flinkiso')->create('users', function ($t) {
            $t->string('id', 36)->primary();
            $t->string('name')->nullable();
            $t->string('username');
            $t->string('password')->nullable();
            $t->integer('is_creator')->default(0);
            $t->integer('is_mr')->default(0);
            $t->integer('is_hod')->default(0);
            $t->integer('is_approver')->default(0);
            $t->integer('is_publisher')->default(0);
            $t->integer('soft_delete')->default(0);
            $t->integer('status')->default(1);
        });

        $this->u = $this->makeUser('tester', 'Secret1!');
    }

    private function makeUser(string $username, string $password): array
    {
        $id = (string) Str::uuid();
        DB::connection('flinkiso')->table('users')->insert([
            'id' => $id, 'name' => ucfirst($username), 'username' => $username,
            'password' => md5(self::SALT . $password), 'soft_delete' => 0, 'status' => 1, 'is_creator' => 1,
        ]);
        return ['id' => $id, 'username' => $username, 'name' => ucfirst($username), 'password' => $password];
    }

    private function sessionReq(array $user): Request
    {
        $r = Request::create('/x', 'GET');
        $r->setLaravelSession($this->app['session']->driver());
        $r->session()->put('flink_user', ['id' => $user['id'], 'username' => $user['username'], 'name' => $user['name']]);
        return $r;
    }

    public function test_admin_area_rejects_non_admin_and_allows_admin(): void
    {
        $roles = app(UserRoleService::class);
        $reviewer = $this->makeUser('rev', 'Secret1!');
        $admin = $this->makeUser('adm', 'Secret1!');
        $roles->setRoles($reviewer['id'], ['reviewer' => true]);
        $roles->setRoles($admin['id'], ['admin' => true]);

        $this->assertFalse($roles->isAdmin($reviewer['id']));
        $this->assertTrue($roles->isAdmin($admin['id']));

        $mw = app(RequireQmsAdmin::class);
        $passed = fn ($req) => response('ok');

        // Reviewer → rejected server-side (403), regardless of hidden menus.
        try {
            $mw->handle($this->sessionReq($reviewer), $passed);
            $this->fail('Reviewer should be rejected from the admin area.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // Admin → allowed through.
        $res = $mw->handle($this->sessionReq($admin), $passed);
        $this->assertSame('ok', $res->getContent());
    }

    public function test_assign_task_action_creates_a_real_task(): void
    {
        Workflow::create([
            'name' => 'Assign', 'trigger_event' => 'incident.created', 'conditions' => [],
            'actions' => [['type' => 'assign_task', 'params' => ['user_id' => $this->u['id'], 'title' => 'Investigate NC']]],
            'active' => true, 'created_by' => $this->u['id'],
        ]);
        $inc = $this->makeIncident();
        app(WorkflowEngine::class)->dispatch('incident.created', ['entity_type' => 'qms_incident', 'entity_id' => $inc->id, 'created_by' => $this->u['id']]);

        $task = Task::where('assigned_to', $this->u['id'])->where('kind', 'task')->first();
        $this->assertNotNull($task, 'assign_task must create a real task, not only a notification.');
        $this->assertSame('open', $task->status);
        $this->assertSame('qms_incident', $task->related_type);
        $this->assertSame($inc->id, $task->related_id);
    }

    public function test_request_approval_action_creates_an_approval_task(): void
    {
        Workflow::create([
            'name' => 'Approve', 'trigger_event' => 'incident.created', 'conditions' => [],
            'actions' => [['type' => 'request_approval', 'params' => ['approver_id' => $this->u['id'], 'title' => 'Approve NC']]],
            'active' => true, 'created_by' => $this->u['id'],
        ]);
        $inc = $this->makeIncident();
        app(WorkflowEngine::class)->dispatch('incident.created', ['entity_type' => 'qms_incident', 'entity_id' => $inc->id, 'created_by' => $this->u['id']]);

        $this->assertSame(1, Task::where('assigned_to', $this->u['id'])->where('kind', 'approval')->count());
    }

    public function test_evidence_download_preserves_original_filename_and_extension(): void
    {
        Storage::fake();
        Storage::put('evidence/abc123.pdf', '%PDF-1.4 test');

        // With an original filename → that exact name is used.
        $e1 = Evidence::create([
            'related_type' => 'qms_incident', 'related_id' => (string) Str::uuid(), 'evidence_type' => 'file',
            'title' => 'Batch record', 'file_path' => 'evidence/abc123.pdf', 'original_name' => 'report.pdf',
            'created_by' => $this->u['id'],
        ]);
        $disp1 = app(EvidenceController::class)->download($e1->id)->headers->get('content-disposition');
        $this->assertStringContainsString('report.pdf', $disp1);

        // Without an original name → the title still gets the real extension.
        $e2 = Evidence::create([
            'related_type' => 'qms_incident', 'related_id' => (string) Str::uuid(), 'evidence_type' => 'file',
            'title' => 'Batch record', 'file_path' => 'evidence/abc123.pdf', 'original_name' => null,
            'created_by' => $this->u['id'],
        ]);
        $disp2 = app(EvidenceController::class)->download($e2->id)->headers->get('content-disposition');
        $this->assertStringContainsString('.pdf', $disp2);
    }

    public function test_reminders_cover_due_soon_not_only_overdue(): void
    {
        // Due in 3 days (upcoming, not overdue).
        Incident::create([
            'reference' => 'INC DUE', 'title' => 'Upcoming', 'type' => 'non_conformity', 'severity' => 'high',
            'status' => 'open', 'created_by' => $this->u['id'], 'detected_by' => $this->u['id'],
            'detected_date' => now()->toDateString(), 'assigned_to' => $this->u['id'],
            'due_date' => now()->addDays(3)->toDateString(),
        ]);
        Capa::create([
            'reference' => 'CAPA DUE', 'title' => 'Upcoming', 'type' => 'corrective', 'priority' => 'high',
            'status' => 'in_progress', 'created_by' => $this->u['id'], 'assigned_to' => $this->u['id'],
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->artisan('qms:overdue-reminders')->assertSuccessful();

        $this->assertTrue(Notification::where('title', 'like', 'Due soon: incident%')->exists());
        $this->assertTrue(Notification::where('title', 'like', 'Due soon: CAPA%')->exists());
    }

    private function makeIncident(string $status = 'open'): Incident
    {
        return Incident::create([
            'reference' => 'INC ' . date('Y') . ' ' . strtoupper(Str::random(4)),
            'title' => 'Test', 'type' => 'non_conformity', 'severity' => 'high',
            'status' => $status, 'created_by' => $this->u['id'], 'detected_by' => $this->u['id'],
            'detected_date' => now()->toDateString(),
        ]);
    }
}

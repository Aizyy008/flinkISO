<?php

namespace Tests\Feature\Qms;

use App\Http\Controllers\Api\Qms\CapaController as ApiCapa;
use App\Http\Controllers\Web\AuditController;
use App\Http\Controllers\Web\CapaController;
use App\Http\Controllers\Web\IncidentController;
use App\Http\Controllers\Web\RiskController;
use App\Http\Controllers\Web\WorkflowController;
use App\Models\Qms\Audit;
use App\Models\Qms\AuditTrail;
use App\Models\Qms\Capa;
use App\Models\Qms\ElectronicSignature;
use App\Models\Qms\Incident;
use App\Models\Qms\Notification;
use App\Models\Qms\Risk;
use App\Models\Qms\Workflow;
use App\Models\Qms\WorkflowRun;
use App\Services\Qms\WorkflowEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Milestone 1.2 (Incident, CAPA, Risk, Workflow, Notifications, Audit) automated
 * acceptance checks — covering the client's raised issues: CAPA effectiveness
 * electronic signature + password re-auth, close-only-after-verify (web & API),
 * incident sync from "investigating", and audit-trail completeness.
 *
 * Runs on an in-memory sqlite DB shared across the app's two connections; the
 * legacy `users` table is created minimally for authentication.
 */
class MilestoneTwoTest extends TestCase
{
    use RefreshDatabase;

    private const SALT = 'testsalt';
    private array $u = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['flinkiso.security_salt' => self::SALT]);
        // Point the flinkiso connection at the same in-memory sqlite as the default
        // (matching grammar + the single shared PDO), so the legacy `users` table
        // and the qms_* tables live in one database for the test.
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

    /** Build a request carrying the acting user in the session (web) or attributes (api). */
    private function req(array $body = [], bool $api = false): Request
    {
        $r = Request::create('/x', 'POST', $body);
        if ($api) {
            $r->attributes->set('flink_user', ['sub' => $this->u['id'], 'username' => $this->u['username'], 'name' => $this->u['name']]);
        } else {
            $r->setLaravelSession($this->app['session']->driver());
            $r->session()->put('flink_user', ['id' => $this->u['id'], 'username' => $this->u['username'], 'name' => $this->u['name']]);
        }
        return $r;
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

    public function test_incident_create_and_status_change(): void
    {
        app(IncidentController::class)->store($this->req(['title' => 'NC', 'type' => 'non_conformity', 'severity' => 'high']));
        $inc = Incident::first();
        $this->assertNotNull($inc);
        $this->assertStringStartsWith('INC', $inc->reference);
        $this->assertSame('open', $inc->status);

        app(IncidentController::class)->updateStatus($this->req(['status' => 'investigating']), $inc->id);
        $this->assertSame('investigating', $inc->fresh()->status);
    }

    public function test_capa_from_open_incident_moves_to_capa_raised(): void
    {
        $inc = $this->makeIncident('open');
        app(CapaController::class)->store($this->req(['title' => 'C', 'type' => 'corrective', 'priority' => 'high', 'incident_id' => $inc->id]));
        $this->assertSame('capa_raised', $inc->fresh()->status);
    }

    public function test_capa_from_investigating_incident_moves_to_capa_raised(): void
    {
        $inc = $this->makeIncident('investigating');
        app(CapaController::class)->store($this->req(['title' => 'C', 'type' => 'corrective', 'priority' => 'high', 'incident_id' => $inc->id]));
        // The fix: an "investigating" incident must also move to capa_raised.
        $this->assertSame('capa_raised', $inc->fresh()->status);
        // And the automatic status change must be audited.
        $this->assertDatabaseHas('qms_audit_trail', ['entity_type' => 'qms_incident', 'entity_id' => $inc->id, 'action' => 'status_change']);
    }

    public function test_capa_effectiveness_requires_correct_password_and_records_signature(): void
    {
        $capa = Capa::create(['reference' => 'CAPA X', 'title' => 'c', 'type' => 'corrective', 'priority' => 'high', 'status' => 'in_progress', 'created_by' => $this->u['id']]);

        // Wrong password → not signed, not verified.
        app(CapaController::class)->verify($this->req(['effectiveness_notes' => 'n', 'verified' => 1, 'password' => 'WRONG']), $capa->id);
        $this->assertFalse((bool) $capa->fresh()->effectiveness_verified);
        $this->assertSame(0, ElectronicSignature::where('entity_id', $capa->id)->count());

        // Correct password → verified + a real electronic signature row.
        app(CapaController::class)->verify($this->req(['effectiveness_notes' => 'confirmed', 'verified' => 1, 'password' => $this->u['password']]), $capa->id);
        $capa->refresh();
        $this->assertTrue((bool) $capa->effectiveness_verified);
        $sig = ElectronicSignature::where('entity_type', 'qms_capa')->where('entity_id', $capa->id)->first();
        $this->assertNotNull($sig, 'An electronic signature must be recorded.');
        $this->assertSame('verified', $sig->meaning);
        $this->assertSame($this->u['username'], $sig->signer_username);
    }

    public function test_web_cannot_close_capa_without_effectiveness_check(): void
    {
        $capa = Capa::create(['reference' => 'CAPA Y', 'title' => 'c', 'type' => 'corrective', 'priority' => 'high', 'status' => 'in_progress', 'created_by' => $this->u['id']]);
        app(CapaController::class)->updateStatus($this->req(['status' => 'closed']), $capa->id);
        $this->assertNotSame('closed', $capa->fresh()->status);
    }

    public function test_api_cannot_close_capa_without_effectiveness_check(): void
    {
        $capa = Capa::create(['reference' => 'CAPA Z', 'title' => 'c', 'type' => 'corrective', 'priority' => 'high', 'status' => 'in_progress', 'created_by' => $this->u['id']]);
        $res = app(ApiCapa::class)->updateStatus($this->req(['status' => 'closed'], true), $capa->id);
        $this->assertSame(422, $res->status());
        $this->assertNotSame('closed', $capa->fresh()->status);

        // After verification the API allows closing.
        $capa->update(['effectiveness_verified' => true]);
        $res2 = app(ApiCapa::class)->updateStatus($this->req(['status' => 'closed'], true), $capa->id);
        $this->assertSame(200, $res2->status());
        $this->assertSame('closed', $capa->fresh()->status);
    }

    public function test_risk_score_is_computed(): void
    {
        app(RiskController::class)->store($this->req(['title' => 'R', 'likelihood' => 4, 'severity' => 5, 'detection' => 3]));
        $this->assertSame(60, (int) Risk::first()->risk_score);
    }

    public function test_workflow_dispatch_runs_actions_and_logs(): void
    {
        Workflow::create([
            'name' => 'Critical → CAPA', 'trigger_event' => 'incident.created',
            'conditions' => [['field' => 'severity', 'op' => '=', 'value' => 'critical']],
            'actions' => [['type' => 'create_capa', 'params' => ['title' => 'Auto']], ['type' => 'notify', 'params' => ['title' => 'x']]],
            'active' => true, 'created_by' => $this->u['id'],
        ]);
        $inc = $this->makeIncident('open');
        app(WorkflowEngine::class)->dispatch('incident.created', ['entity_type' => 'qms_incident', 'entity_id' => $inc->id, 'severity' => 'critical', 'created_by' => $this->u['id']]);

        $this->assertGreaterThan(0, WorkflowRun::where('status', 'completed')->count());
        $this->assertGreaterThan(0, Capa::where('incident_id', $inc->id)->count());
        $this->assertGreaterThan(0, Notification::count());
    }

    public function test_workflow_rule_create_and_toggle_are_audited(): void
    {
        app(WorkflowController::class)->store($this->req([
            'name' => 'Rule', 'trigger_event' => 'incident.created', 'action_notify' => 1, 'notify_title' => 'n',
        ]));
        $wf = Workflow::first();
        $this->assertNotNull($wf);
        $this->assertDatabaseHas('qms_audit_trail', ['entity_type' => 'qms_workflow', 'entity_id' => $wf->id, 'action' => 'create']);

        app(WorkflowController::class)->toggle($this->req(), $wf->id);
        $this->assertDatabaseHas('qms_audit_trail', ['entity_type' => 'qms_workflow', 'entity_id' => $wf->id, 'action' => 'toggle']);
    }

    public function test_workflow_supports_assign_task_and_request_approval(): void
    {
        app(WorkflowController::class)->store($this->req([
            'name' => 'R2', 'trigger_event' => 'incident.created',
            'action_assign_task' => 1, 'task_title' => 't',
            'action_request_approval' => 1, 'approval_title' => 'a',
        ]));
        $types = array_column(Workflow::first()->actions, 'type');
        $this->assertContains('assign_task', $types);
        $this->assertContains('request_approval', $types);
    }

    public function test_audit_checklist_response_is_recorded_in_audit_trail(): void
    {
        $audit = Audit::create(['reference' => 'AUD 1', 'title' => 'a', 'audit_type' => 'internal', 'status' => 'scheduled', 'created_by' => $this->u['id']]);
        app(AuditController::class)->addChecklistItem($this->req(['question' => 'Q1']), $audit->id);
        $item = $audit->checklistItems()->first();
        $this->assertDatabaseHas('qms_audit_trail', ['entity_type' => 'qms_audit', 'action' => 'checklist_item_added']);

        app(AuditController::class)->recordResponse($this->req(['response' => 'nonconform']), $audit->id, $item->id);
        $this->assertDatabaseHas('qms_audit_trail', ['entity_type' => 'qms_audit', 'action' => 'checklist_response']);
    }
}

<?php

namespace Tests\Feature\Qms;

use App\Http\Controllers\Web\CalibrationController;
use App\Http\Controllers\Web\HaccpController;
use App\Http\Controllers\Web\KpiController;
use App\Http\Controllers\Web\TrainingController;
use App\Models\Qms\Asset;
use App\Models\Qms\Capa;
use App\Models\Qms\ElectronicSignature;
use App\Models\Qms\HaccpCcp;
use App\Models\Qms\HaccpPlan;
use App\Models\Qms\Incident;
use App\Models\Qms\Kpi;
use App\Models\Qms\Training;
use App\Models\Qms\TrainingRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Milestone 2.1 (KPI, Training, Calibration, HACCP) automated acceptance checks,
 * including the HACCP CCP deviation -> Incident + CAPA chain and the HACCP plan
 * approval electronic signature (password re-auth + signatures record).
 */
class MilestoneThreeTest extends TestCase
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
            $t->integer('soft_delete')->default(0);
            $t->integer('status')->default(1);
        });
        $id = (string) Str::uuid();
        DB::connection('flinkiso')->table('users')->insert([
            'id' => $id, 'name' => 'Tester', 'username' => 'tester',
            'password' => md5(self::SALT . 'Secret1!'), 'soft_delete' => 0, 'status' => 1,
        ]);
        $this->u = ['id' => $id, 'username' => 'tester', 'name' => 'Tester', 'password' => 'Secret1!'];
    }

    private function req(array $body = []): Request
    {
        $r = Request::create('/x', 'POST', $body);
        $r->setLaravelSession($this->app['session']->driver());
        $r->session()->put('flink_user', ['id' => $this->u['id'], 'username' => $this->u['username'], 'name' => $this->u['name']]);
        return $r;
    }

    public function test_kpi_status_is_computed_from_results(): void
    {
        app(KpiController::class)->store($this->req([
            'name' => 'On-Time Delivery', 'area' => 'quality', 'direction' => 'higher_better',
            'aggregation' => 'monthly', 'target_value' => 95, 'warning_threshold' => 90, 'critical_threshold' => 85,
        ]));
        $kpi = Kpi::first();
        $this->assertNotNull($kpi);

        app(KpiController::class)->storeResult($this->req(['period_label' => '2026-01', 'period_date' => '2026-01-31', 'value' => 96]), $kpi->id);
        $this->assertSame('on_target', $kpi->fresh()->statusFor(96));
        $this->assertSame('warning', $kpi->statusFor(88));
        $this->assertSame('critical', $kpi->statusFor(80));
    }

    public function test_training_completion_sets_expiry_and_competency(): void
    {
        app(TrainingController::class)->store($this->req(['title' => 'Food Hygiene', 'validity_months' => 12]));
        $training = Training::first();
        app(TrainingController::class)->assign($this->req(['user_id' => $this->u['id']]), $training->id);
        $record = TrainingRecord::first();

        app(TrainingController::class)->complete($this->req(['completed_date' => now()->toDateString(), 'result' => 'Passed']), $record->id);
        $record->refresh();
        $this->assertSame('completed', $record->status);
        $this->assertNotNull($record->expiry_date);
        $this->assertSame('valid', $record->competency());
    }

    public function test_duplicate_training_assignment_is_prevented(): void
    {
        app(TrainingController::class)->store($this->req(['title' => 'Food Hygiene', 'validity_months' => 12]));
        $training = Training::first();
        // Assign the same user twice.
        app(TrainingController::class)->assign($this->req(['user_id' => $this->u['id']]), $training->id);
        app(TrainingController::class)->assign($this->req(['user_id' => $this->u['id']]), $training->id);
        // Only one active assignment is created.
        $this->assertSame(1, TrainingRecord::where('training_id', $training->id)->where('user_id', $this->u['id'])->count());
    }

    public function test_calibration_sets_next_due_and_status(): void
    {
        app(CalibrationController::class)->store($this->req(['name' => 'Thermometer', 'requires_calibration' => 1, 'calibration_frequency_months' => 6]));
        $asset = Asset::first();
        app(CalibrationController::class)->record($this->req(['performed_date' => now()->toDateString(), 'result' => 'pass']), $asset->id);
        $asset->refresh();
        $this->assertNotNull($asset->next_due_date);
        $this->assertSame('ok', $asset->calibrationStatus());
    }

    private function makePlanWithCcp(): HaccpCcp
    {
        app(HaccpController::class)->store($this->req(['product' => 'Fresh Milk 3.5%']));
        $plan = HaccpPlan::first();
        app(HaccpController::class)->addCcp($this->req([
            'name' => 'Pasteurization', 'critical_limit' => '72C for 15s', 'limit_min' => 72, 'corrective_action' => 'reprocess',
        ]), $plan->id);
        return HaccpCcp::first();
    }

    public function test_haccp_ccp_within_limit_creates_no_incident(): void
    {
        $ccp = $this->makePlanWithCcp();
        app(HaccpController::class)->logCcp($this->req(['measured_value' => 74]), $ccp->id);
        $this->assertSame(0, Incident::count());
    }

    public function test_haccp_ccp_deviation_raises_incident_and_capa(): void
    {
        $ccp = $this->makePlanWithCcp();
        app(HaccpController::class)->logCcp($this->req(['measured_value' => 68]), $ccp->id);
        $this->assertSame(1, Incident::where('source', 'ccp')->count());
        $this->assertSame(1, Capa::count());
    }

    public function test_haccp_plan_approval_requires_password_and_records_signature(): void
    {
        app(HaccpController::class)->store($this->req(['product' => 'Cheese']));
        $plan = HaccpPlan::first();

        // Wrong password -> not approved, no signature.
        app(HaccpController::class)->transition($this->req(['to' => 'approved', 'password' => 'WRONG']), $plan->id);
        $this->assertNotSame('approved', $plan->fresh()->status);
        $this->assertSame(0, ElectronicSignature::where('entity_id', $plan->id)->count());

        // Correct password -> approved + electronic signature recorded.
        app(HaccpController::class)->transition($this->req(['to' => 'approved', 'password' => $this->u['password']]), $plan->id);
        $plan->refresh();
        $this->assertSame('approved', $plan->status);
        $sig = ElectronicSignature::where('entity_type', 'qms_haccp_plan')->where('entity_id', $plan->id)->first();
        $this->assertNotNull($sig);
        $this->assertSame('approved', $sig->meaning);
    }
}

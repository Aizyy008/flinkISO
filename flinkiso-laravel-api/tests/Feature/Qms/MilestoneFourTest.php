<?php

namespace Tests\Feature\Qms;

use App\Http\Controllers\Api\Qms\AiController;
use App\Http\Controllers\Web\ValidationController;
use App\Models\Qms\ElectronicSignature;
use App\Models\Qms\Validation;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Milestone 2.2 (GMP/Validation, REST API + JWT, AI microservice) automated
 * acceptance checks: GMP approval electronic signature, JWT issue/verify, and
 * graceful AI degradation when the microservice is disabled.
 */
class MilestoneFourTest extends TestCase
{
    use RefreshDatabase;

    private const SALT = 'testsalt';
    private array $u = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['flinkiso.security_salt' => self::SALT, 'flinkiso.jwt_secret' => str_repeat('k', 48), 'flinkiso.jwt_ttl_minutes' => 120]);
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

    public function test_validation_approval_requires_password_and_records_signature(): void
    {
        app(ValidationController::class)->store($this->req(['type' => 'equipment_oq', 'title' => 'Pasteurizer OQ']));
        $v = Validation::first();
        $this->assertNotNull($v);

        // Wrong password -> not approved, no signature.
        app(ValidationController::class)->transition($this->req(['to' => 'approved', 'password' => 'WRONG']), $v->id);
        $this->assertNotSame('approved', $v->fresh()->status);
        $this->assertSame(0, ElectronicSignature::where('entity_id', $v->id)->count());

        // Correct password -> approved + electronic signature recorded.
        app(ValidationController::class)->transition($this->req(['to' => 'approved', 'password' => $this->u['password']]), $v->id);
        $v->refresh();
        $this->assertSame('approved', $v->status);
        $sig = ElectronicSignature::where('entity_type', 'qms_validation')->where('entity_id', $v->id)->first();
        $this->assertNotNull($sig);
        $this->assertSame('approved', $sig->meaning);
    }

    public function test_jwt_issue_and_verify(): void
    {
        $jwt = app(JwtService::class);
        $token = $jwt->issue(['sub' => $this->u['id'], 'username' => 'tester']);
        $this->assertIsString($token);

        $claims = $jwt->verify($token);
        $this->assertNotNull($claims);
        $this->assertSame('tester', $claims['username']);

        $this->assertNull($jwt->verify($token . 'tampered'), 'A tampered token must not verify.');
    }

    public function test_ai_endpoints_degrade_gracefully_when_disabled(): void
    {
        config(['ai.enabled' => false]);
        $res = app(AiController::class)->riskScore($this->req(['likelihood' => 4, 'severity' => 5, 'detection' => 3]));
        $this->assertSame(503, $res->status(), 'AI endpoints must return a clean 503 when the service is disabled, not a 500.');
    }
}

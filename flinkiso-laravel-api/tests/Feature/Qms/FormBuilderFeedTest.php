<?php

namespace Tests\Feature\Qms;

use App\Http\Controllers\Web\FormBuilderController;
use App\Models\Qms\Audit;
use App\Models\Qms\Capa;
use App\Models\Qms\Form;
use App\Models\Qms\HaccpPlan;
use App\Models\Qms\Incident;
use App\Models\Qms\Risk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Form Builder — a submission feeds the configured QMS record. Covers all wired
 * targets: Incident, Risk, CAPA, Audit, HACCP (Validation is on milestone_1.4).
 */
class FormBuilderFeedTest extends TestCase
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
            'password' => md5(self::SALT . 'x'), 'soft_delete' => 0, 'status' => 1,
        ]);
        $this->u = ['id' => $id, 'username' => 'tester', 'name' => 'Tester'];
    }

    private function makeForm(string $feeds): Form
    {
        $form = Form::create([
            'reference' => 'FRM 2026 ' . strtoupper(Str::random(4)),
            'name' => 'Test Form', 'status' => 'active',
            'feeds_record_type' => $feeds, 'created_by' => $this->u['id'],
        ]);
        $form->fields()->create([
            'field_key' => 'title', 'label' => 'Title', 'field_type' => 'text',
            'required' => true, 'sort_order' => 0,
        ]);
        return $form;
    }

    private function submit(Form $form, array $body): void
    {
        $r = Request::create('/x', 'POST', $body);
        $r->setLaravelSession($this->app['session']->driver());
        $r->session()->put('flink_user', $this->u);
        app(FormBuilderController::class)->submit($r, $form->id);
    }

    public function test_a_submission_feeds_each_wired_record_type(): void
    {
        $targets = [
            'incident' => Incident::class,
            'risk' => Risk::class,
            'capa' => Capa::class,
            'audit' => Audit::class,
            'haccp' => HaccpPlan::class,
        ];

        foreach ($targets as $feeds => $model) {
            $before = $model::count();
            $form = $this->makeForm($feeds);
            $this->submit($form, ['f_title' => ucfirst($feeds) . ' from form']);
            $this->assertSame($before + 1, $model::count(), "The '{$feeds}' form must create a {$model} record.");

            // The submission is linked back to the created record.
            $this->assertDatabaseHas('qms_form_submissions', ['form_id' => $form->id, 'linked_record_type' => 'qms_' . ($feeds === 'haccp' ? 'haccp_plan' : $feeds)]);
        }
    }
}

<?php

namespace Tests\Feature\Qms;

use App\Http\Controllers\Web\FormBuilderController;
use App\Models\Qms\Form;
use App\Models\Qms\Validation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Form Builder — a submission feeds a GMP / Validation log. Validation is a
 * milestone_1.4 (M4) module, so this target lives on this branch only.
 */
class FormBuilderValidationFeedTest extends TestCase
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

    public function test_a_submission_feeds_a_validation_log(): void
    {
        $form = Form::create([
            'reference' => 'FRM 2026 ' . strtoupper(Str::random(4)),
            'name' => 'Validation Form', 'status' => 'active',
            'feeds_record_type' => 'validation', 'created_by' => $this->u['id'],
        ]);
        $form->fields()->create(['field_key' => 'title', 'label' => 'Title', 'field_type' => 'text', 'required' => true, 'sort_order' => 0]);

        $before = Validation::count();
        $r = Request::create('/x', 'POST', ['f_title' => 'Autoclave OQ']);
        $r->setLaravelSession($this->app['session']->driver());
        $r->session()->put('flink_user', $this->u);
        app(FormBuilderController::class)->submit($r, $form->id);

        $this->assertSame($before + 1, Validation::count(), 'The validation form must create a Validation record.');
        $this->assertDatabaseHas('qms_form_submissions', ['form_id' => $form->id, 'linked_record_type' => 'qms_validation']);
    }
}

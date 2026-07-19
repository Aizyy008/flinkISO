<?php

namespace Database\Seeders;

use App\Models\Qms\Form;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Sample active form for the drag & drop Form Builder. Idempotent. */
class FormBuilderDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Form::where('name', 'Line Inspection Report')->exists()) {
            $this->command->info('Form Builder demo already exists, skipping.');
            return;
        }
        $u = DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->first();
        $form = Form::create([
            'reference' => 'FRM ' . date('Y') . ' 0001', 'name' => 'Line Inspection Report',
            'description' => 'Daily production line inspection. A "Fail" result raises an Incident.',
            'category' => 'Inspection', 'status' => 'active', 'feeds_record_type' => 'incident',
            'trigger_event' => 'form.submitted', 'created_by' => $u?->id,
        ]);
        $fields = [
            ['title', 'Inspection title', 'text', null, true],
            ['line', 'Production line', 'dropdown', ['Line 1', 'Line 2', 'Line 3'], true],
            ['result', 'Overall result', 'radio', ['Pass', 'Fail'], true],
            ['severity', 'Severity (if fail)', 'dropdown', ['low', 'medium', 'high', 'critical'], false],
            ['notes', 'Notes', 'textarea', null, false],
            ['checks', 'Checklist', 'repeatable', ['Check', 'OK?'], false],
            ['photo', 'Photo evidence', 'file', null, false],
            ['sign', 'Inspector signature', 'signature', null, false],
        ];
        foreach ($fields as $i => [$key, $label, $type, $opts, $req]) {
            $form->fields()->create([
                'field_key' => $key, 'label' => $label, 'field_type' => $type,
                'options' => $opts, 'required' => $req, 'sort_order' => $i,
                'cond_field' => $key === 'severity' ? 'result' : null,
                'cond_op' => $key === 'severity' ? '=' : null,
                'cond_value' => $key === 'severity' ? 'Fail' : null,
            ]);
        }
        $this->command->info('Seeded demo form: Line Inspection Report (active, feeds incident).');
    }
}

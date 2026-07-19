<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Form;
use App\Models\Qms\FormSubmission;
use App\Models\Qms\Incident;
use App\Models\Qms\Risk;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\WorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Drag & Drop Form Builder (FlickISO §6). Build reusable forms with a full field
 * set (incl. signature + repeatable items) and conditional visibility; submissions
 * are stored, can feed a QMS record, and can trigger a workflow.
 */
class FormBuilderController extends Controller
{
    public function __construct(
        private AuditTrailService $audit,
        private WorkflowEngine $workflows,
    ) {}

    private function user(Request $r): array { return $r->session()->get('flink_user'); }

    /** Field types the builder offers. */
    public const FIELD_TYPES = [
        'text' => 'Text', 'textarea' => 'Text area', 'number' => 'Number',
        'dropdown' => 'Dropdown', 'multiselect' => 'Multi-select', 'radio' => 'Radio',
        'checkbox' => 'Checkbox', 'date' => 'Date', 'datetime' => 'Date & time',
        'file' => 'File upload', 'signature' => 'Signature', 'repeatable' => 'Repeatable items',
        'section' => 'Section heading',
    ];

    public function index()
    {
        $forms = Form::withCount('submissions')->latest()->paginate(20);
        return view('form_builder.index', compact('forms'));
    }

    public function create()
    {
        return view('form_builder.builder', ['form' => null, 'fields' => []]);
    }

    public function edit(string $id)
    {
        $form = Form::with('fields')->findOrFail($id);
        return view('form_builder.builder', ['form' => $form, 'fields' => $form->fields]);
    }

    public function store(Request $request)
    {
        return $this->persist($request, null);
    }

    public function update(Request $request, string $id)
    {
        return $this->persist($request, Form::findOrFail($id));
    }

    /** Save a form definition + its fields (fields arrive as a JSON array). */
    private function persist(Request $request, ?Form $form)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:60',
            'status' => 'required|in:draft,active,archived',
            'feeds_record_type' => 'nullable|in:incident,risk',
            'trigger_event' => 'nullable|string|max:80',
            'fields_json' => 'required|string',
        ]);
        $u = $this->user($request);
        $fields = json_decode($data['fields_json'], true) ?: [];

        $form = DB::transaction(function () use ($form, $data, $fields, $u) {
            if (!$form) {
                $form = Form::create([
                    'reference' => $this->nextReference(),
                    'created_by' => $u['id'],
                ] + collect($data)->only(['name', 'description', 'category', 'status', 'feeds_record_type', 'trigger_event'])->toArray());
            } else {
                $form->update(collect($data)->only(['name', 'description', 'category', 'status', 'feeds_record_type', 'trigger_event'])->toArray());
                $form->fields()->delete();
            }

            foreach ($fields as $i => $f) {
                if (empty($f['label']) || empty($f['field_type'])) {
                    continue;
                }
                $form->fields()->create([
                    'field_key' => $f['field_key'] ?? Str::slug($f['label'], '_') . '_' . $i,
                    'label' => $f['label'],
                    'field_type' => $f['field_type'],
                    'options' => $f['options'] ?? null,
                    'required' => (bool) ($f['required'] ?? false),
                    'placeholder' => $f['placeholder'] ?? null,
                    'help_text' => $f['help_text'] ?? null,
                    'sort_order' => $i,
                    'cond_field' => $f['cond_field'] ?? null,
                    'cond_op' => $f['cond_op'] ?? null,
                    'cond_value' => $f['cond_value'] ?? null,
                ]);
            }
            return $form;
        });

        $this->audit->record('qms_form', $form->id, $form->wasRecentlyCreated ? 'create' : 'update',
            ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['name' => $form->name, 'fields' => count($fields)]]);

        return redirect('/form-builder/' . $form->id . '/edit')->with('ok', "Form '{$form->name}' saved with " . $form->fields()->count() . ' field(s).');
    }

    public function destroy(Request $request, string $id)
    {
        $form = Form::findOrFail($id);
        $form->fields()->delete();
        $form->delete();
        return redirect('/form-builder')->with('ok', 'Form deleted.');
    }

    /** Public-ish fill screen (still session protected). */
    public function fill(string $id)
    {
        $form = Form::with('fields')->findOrFail($id);
        abort_if($form->status !== 'active', 403, 'This form is not active.');
        return view('form_builder.fill', compact('form'));
    }

    /** Process a submission: store, optionally feed a record, optionally trigger a workflow. */
    public function submit(Request $request, string $id)
    {
        $form = Form::with('fields')->findOrFail($id);
        abort_if($form->status !== 'active', 403);
        $u = $this->user($request);

        // Validate required, visible fields.
        $rules = [];
        foreach ($form->fields as $f) {
            if ($f->required && !in_array($f->field_type, ['section'], true)) {
                $rules['f_' . $f->field_key] = 'required';
            }
        }
        $request->validate($rules);

        // Collect the data (handle files + signatures + repeatables).
        $data = [];
        foreach ($form->fields as $f) {
            if ($f->field_type === 'section') {
                continue;
            }
            $key = 'f_' . $f->field_key;
            if ($f->field_type === 'file' && $request->hasFile($key)) {
                $data[$f->field_key] = $request->file($key)->store('form-uploads');
            } elseif ($f->field_type === 'multiselect') {
                $data[$f->field_key] = (array) $request->input($key, []);
            } else {
                $data[$f->field_key] = $request->input($key);
            }
        }

        $submission = FormSubmission::create([
            'reference' => $this->nextSubmissionReference(),
            'form_id' => $form->id,
            'data' => $data,
            'submitted_by' => $u['id'],
            'ip_address' => $request->ip(),
        ]);

        // Feed a QMS record if configured.
        $linked = $this->feedRecord($form, $data, $u);
        if ($linked) {
            $submission->update(['linked_record_type' => $linked['type'], 'linked_record_id' => $linked['id']]);
        }

        // Trigger a workflow if configured.
        if ($form->trigger_event) {
            $this->workflows->dispatch($form->trigger_event, [
                'entity_type' => 'qms_form_submission', 'entity_id' => $submission->id,
                'form' => $form->reference, 'created_by' => $u['id'],
            ] + $data);
        }

        $this->audit->record('qms_form_submission', $submission->id, 'submit',
            ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['form' => $form->reference, 'linked' => $linked['ref'] ?? null]]);

        return redirect('/form-builder/' . $form->id . '/submissions')
            ->with('ok', "Submission {$submission->reference} recorded." . (!empty($linked) ? " Created {$linked['ref']}." : ''));
    }

    public function submissions(string $id)
    {
        $form = Form::with('fields')->findOrFail($id);
        $submissions = $form->submissions()->paginate(30);
        return view('form_builder.submissions', compact('form', 'submissions'));
    }

    /** Best-effort creation of a QMS record from the submission (spec: forms feed records). */
    private function feedRecord(Form $form, array $data, array $u): ?array
    {
        if (!$form->feeds_record_type) {
            return null;
        }
        // Pick a title from a field keyed/labelled title, else the form name.
        $title = $data['title'] ?? ($data['name'] ?? $form->name . ' submission');
        $description = collect($data)->map(fn ($v, $k) => is_array($v) ? "$k: " . implode(', ', $v) : "$k: $v")->implode("\n");

        if ($form->feeds_record_type === 'incident') {
            $inc = Incident::create([
                'reference' => 'INC ' . date('Y') . ' ' . sprintf('%04d', Incident::where('reference', 'like', 'INC ' . date('Y') . ' %')->count() + 1),
                'type' => 'non_conformity', 'title' => Str::limit($title, 200), 'description' => $description,
                'severity' => $data['severity'] ?? 'medium', 'source' => 'Form ' . $form->reference,
                'status' => 'open', 'detected_by' => $u['id'], 'detected_date' => now()->toDateString(), 'created_by' => $u['id'],
            ]);
            return ['type' => 'qms_incident', 'id' => $inc->id, 'ref' => $inc->reference];
        }
        if ($form->feeds_record_type === 'risk') {
            $risk = new Risk([
                'reference' => 'RISK ' . date('Y') . ' ' . sprintf('%04d', Risk::where('reference', 'like', 'RISK ' . date('Y') . ' %')->count() + 1),
                'title' => Str::limit($title, 200), 'description' => $description,
                'likelihood' => (int) ($data['likelihood'] ?? 3), 'severity' => (int) ($data['severity'] ?? 3),
                'detection' => (int) ($data['detection'] ?? 3), 'status' => 'open', 'created_by' => $u['id'],
            ]);
            $risk->recalculate();
            $risk->save();
            return ['type' => 'qms_risk', 'id' => $risk->id, 'ref' => $risk->reference];
        }
        return null;
    }

    private function nextReference(): string
    {
        $year = date('Y');
        return sprintf('FRM %s %04d', $year, Form::where('reference', 'like', "FRM $year %")->count() + 1);
    }

    private function nextSubmissionReference(): string
    {
        $year = date('Y');
        return sprintf('SUB %s %05d', $year, FormSubmission::where('reference', 'like', "SUB $year %")->count() + 1);
    }
}

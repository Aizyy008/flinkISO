<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Audit;
use App\Models\Qms\AuditChecklistItem;
use App\Models\Qms\AuditFinding;
use App\Models\Qms\AuditProgram;
use App\Models\Qms\Evidence;
use App\Models\Qms\Incident;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\Notifier;
use App\Services\Qms\WorkflowEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Audit Management: annual program, scheduled audits (assigned to auditors),
 * multi-section checklist, and findings that raise a Non-Conformity (Incident) →
 * CAPA. Report PDF export. Evidence via the shared store.
 */
class AuditController extends Controller
{
    public function __construct(
        private AuditTrailService $audit,
        private Notifier $notifier,
        private WorkflowEngine $workflows,
    ) {}

    private function user(Request $r): array { return $r->session()->get('flink_user'); }

    private function refData(): array
    {
        return [
            'standards' => DB::connection('flinkiso')->table('standards')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name']),
            'users' => DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name', 'username']),
            'programs' => AuditProgram::orderByDesc('year')->get(),
        ];
    }

    public function index(Request $request)
    {
        $q = Audit::with('program')->withCount('findings')->latest();
        if ($request->filled('status')) { $q->where('status', $request->string('status')); }
        if ($request->filled('audit_type')) { $q->where('audit_type', $request->string('audit_type')); }
        $audits = $q->paginate(20)->withQueryString();
        $programs = AuditProgram::withCount('audits')->orderByDesc('year')->get();
        return view('audits.index', compact('audits', 'programs'));
    }

    public function create()
    {
        return view('audits.create', $this->refData());
    }

    /** Create an annual audit program. */
    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'title' => 'required|string|max:255',
            'objectives' => 'nullable|string',
        ]);
        $u = $this->user($request);
        $program = AuditProgram::create($data + [
            'reference' => 'AP ' . $data['year'] . ' ' . sprintf('%02d', AuditProgram::where('year', $data['year'])->count() + 1),
            'status' => 'active', 'created_by' => $u['id'],
        ]);
        $this->audit->record('qms_audit_program', $program->id, 'create', ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['new' => $program->only(['reference', 'title'])]]);
        return back()->with('ok', "Audit program {$program->reference} created.");
    }

    /** Schedule an audit (assign to an auditor). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'audit_type' => 'required|in:internal,external,supplier',
            'program_id' => 'nullable|string|max:36',
            'standard' => 'nullable|string|max:40',
            'scope' => 'nullable|string',
            'lead_auditor_id' => 'nullable|string|max:36',
            'auditor_id' => 'nullable|string|max:36',
            'planned_date' => 'nullable|date',
            'related_process' => 'nullable|string|max:255',
            'related_site' => 'nullable|string|max:255',
            'related_department' => 'nullable|string|max:255',
            'related_clause' => 'nullable|string|max:255',
        ]);
        $u = $this->user($request);
        $data['reference'] = $this->nextReference();
        $data['status'] = 'scheduled';
        $data['created_by'] = $u['id'];
        $audit = Audit::create($data);

        $this->audit->record('qms_audit', $audit->id, 'create', ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['new' => $audit->only(['reference', 'title', 'audit_type'])]]);

        // Notify the assigned auditor of the schedule.
        foreach (array_filter([$audit->auditor_id, $audit->lead_auditor_id]) as $auditorId) {
            $this->notifier->notify($auditorId, 'assignment',
                "Audit assigned: {$audit->reference}",
                $audit->title . ($audit->planned_date ? ' — planned ' . $audit->planned_date->format('d M Y') : ''),
                'qms_audit', $audit->id, true);
        }

        return redirect('/audits/' . $audit->id)->with('ok', "Audit {$audit->reference} scheduled.");
    }

    public function show(Request $request, string $id)
    {
        $audit = Audit::with(['program', 'checklistItems', 'findings.incident'])->findOrFail($id);
        $evidence = Evidence::where('related_type', 'qms_audit')->where('related_id', $id)->latest()->get();
        $trail = \App\Models\Qms\AuditTrail::where('entity_type', 'qms_audit')->where('entity_id', $id)->orderByDesc('seq')->get();
        return view('audits.show', ['audit' => $audit, 'evidence' => $evidence, 'trail' => $trail] + $this->refData());
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,closed',
            'result' => 'nullable|in:conform,minor_nc,major_nc',
            'summary' => 'nullable|string',
        ]);
        $audit = Audit::findOrFail($id);
        $from = $audit->status;
        $audit->fill($data);
        if ($data['status'] === 'completed' && !$audit->actual_date) { $audit->actual_date = now()->toDateString(); }
        $audit->save();
        $this->audit->record('qms_audit', $audit->id, 'status_change', ['user_id' => $this->user($request)['id'], 'username' => $this->user($request)['username'], 'changes' => ['status' => ['old' => $from, 'new' => $audit->status]]]);
        return back()->with('ok', "Audit moved to {$audit->status}.");
    }

    /** Add a multi-section checklist item. */
    public function addChecklistItem(Request $request, string $id)
    {
        $data = $request->validate([
            'section' => 'nullable|string|max:255',
            'clause_ref' => 'nullable|string|max:255',
            'question' => 'required|string',
        ]);
        $audit = Audit::findOrFail($id);
        $audit->checklistItems()->create([
            'section' => $data['section'] ?: 'General',
            'clause_ref' => $data['clause_ref'] ?? null,
            'question' => $data['question'],
            'sort_order' => (int) $audit->checklistItems()->max('sort_order') + 1,
            'created_by' => $this->user($request)['id'],
        ]);
        return back()->with('ok', 'Checklist item added.');
    }

    /** Record the conformity response for a checklist item. */
    public function recordResponse(Request $request, string $id, string $itemId)
    {
        $data = $request->validate([
            'response' => 'required|in:conform,nonconform,observation,na',
            'notes' => 'nullable|string',
        ]);
        $item = AuditChecklistItem::where('audit_id', $id)->where('id', $itemId)->firstOrFail();
        $item->update($data);
        return back()->with('ok', 'Response recorded.');
    }

    /**
     * Raise a finding. A nonconformity finding creates a Non-Conformity Incident
     * (which can then spawn a CAPA), wiring findings → NC → CAPA.
     */
    public function addFinding(Request $request, string $id)
    {
        $data = $request->validate([
            'finding_type' => 'required|in:nonconformity,observation,ofi',
            'severity' => 'required|in:minor,major,critical',
            'description' => 'required|string',
            'clause_ref' => 'nullable|string|max:255',
            'checklist_item_id' => 'nullable|string|max:36',
        ]);
        $audit = Audit::findOrFail($id);
        $u = $this->user($request);

        $finding = $audit->findings()->create($data + [
            'reference' => $this->nextFindingReference(),
            'status' => 'open',
            'created_by' => $u['id'],
        ]);

        // Nonconformity finding → raise an NC Incident (→ CAPA via the incident flow).
        if ($data['finding_type'] === 'nonconformity') {
            $sevMap = ['minor' => 'low', 'major' => 'high', 'critical' => 'critical'];
            $incident = Incident::create([
                'reference' => $this->nextIncidentReference(),
                'type' => 'non_conformity',
                'title' => "Audit NC: {$audit->reference} — " . \Illuminate\Support\Str::limit($data['description'], 80),
                'description' => $data['description'],
                'severity' => $sevMap[$data['severity']] ?? 'medium',
                'source' => 'Internal audit ' . $audit->reference,
                'status' => 'open',
                'detected_by' => $u['id'],
                'detected_date' => now()->toDateString(),
                'created_by' => $u['id'],
            ]);
            $finding->update(['incident_id' => $incident->id]);

            $this->audit->record('qms_incident', $incident->id, 'create', ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['new' => ['reference' => $incident->reference, 'from_audit' => $audit->reference]]]);
            $this->workflows->dispatch('incident.created', [
                'entity_type' => 'qms_incident', 'entity_id' => $incident->id,
                'severity' => $incident->severity, 'type' => $incident->type, 'created_by' => $u['id'],
            ]);
        }

        $this->audit->record('qms_audit', $audit->id, 'finding', ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['finding' => $finding->reference, 'incident' => $finding->incident_id]]);

        return back()->with('ok', "Finding {$finding->reference} recorded." . ($finding->incident_id ? ' Non-conformity raised for CAPA.' : ''));
    }

    public function report(string $id)
    {
        $audit = Audit::with(['program', 'checklistItems', 'findings.incident'])->findOrFail($id);
        $standard = $audit->standard;
        $pdf = Pdf::loadView('audits.pdf', compact('audit', 'standard'));
        return $pdf->download($audit->reference . '-audit-report.pdf');
    }

    private function nextReference(): string
    {
        $year = date('Y');
        return sprintf('AUD %s %04d', $year, Audit::where('reference', 'like', "AUD $year %")->count() + 1);
    }

    private function nextFindingReference(): string
    {
        $year = date('Y');
        return sprintf('AF %s %04d', $year, AuditFinding::where('reference', 'like', "AF $year %")->count() + 1);
    }

    private function nextIncidentReference(): string
    {
        $year = date('Y');
        return sprintf('INC %s %04d', $year, Incident::where('reference', 'like', "INC $year %")->count() + 1);
    }
}

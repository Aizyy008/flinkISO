<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\AuditTrail;
use App\Models\Qms\Evidence;
use App\Models\Qms\Incident;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\Notifier;
use App\Services\Qms\WorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncidentController extends Controller
{
    public function __construct(
        private AuditTrailService $audit,
        private WorkflowEngine $workflows,
        private Notifier $notifier,
    ) {}

    private function user(Request $r): array
    {
        return $r->session()->get('flink_user');
    }

    private function users()
    {
        return DB::connection('flinkiso')->table('users')->where('soft_delete', 0)
            ->orderBy('name')->get(['id', 'name', 'username']);
    }

    private function userName($users, $id): ?string
    {
        $u = $users->firstWhere('id', $id);
        return $u ? ($u->name ?: $u->username) : null;
    }

    public function index(Request $request)
    {
        $q = Incident::withCount('capas')->latest();
        if ($request->filled('status')) $q->where('status', $request->string('status'));
        if ($request->filled('type')) $q->where('type', $request->string('type'));
        $incidents = $q->paginate(20)->withQueryString();
        return view('incidents.index', compact('incidents'));
    }

    public function create()
    {
        return view('incidents.create', ['users' => $this->users()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:non_conformity,deviation,incident,complaint,near_miss',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:60',
            'root_cause' => 'nullable|string',
            'containment_action' => 'nullable|string',
            'assigned_to' => 'nullable|string|max:36',
            'due_date' => 'nullable|date',
            'iso_standard' => 'nullable|in:' . implode(',', array_keys(config('iso_overlays'))),
            'overlay' => 'nullable|array',
        ]);
        // Keep only the overlay fields defined for the chosen standard.
        if (!empty($data['iso_standard'])) {
            $allowed = array_keys(config("iso_overlays.{$data['iso_standard']}.fields", []));
            $data['iso_overlay'] = array_filter(
                array_intersect_key($request->input('overlay', []), array_flip($allowed)),
                fn ($v) => $v !== null && $v !== ''
            ) ?: null;
        }
        unset($data['overlay']);
        $u = $this->user($request);
        $data['reference'] = $this->nextReference();
        $data['status'] = 'open';
        $data['created_by'] = $u['id'];
        $data['detected_by'] = $u['id'];
        $data['detected_date'] = now()->toDateString();

        $incident = Incident::create($data);

        $this->audit->record('qms_incident', $incident->id, 'create', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['new' => $incident->only(['reference', 'title', 'type', 'severity'])],
        ]);

        // Reusable workflow engine: rules on incident.created (e.g. critical -> auto CAPA + notify).
        $this->workflows->dispatch('incident.created', [
            'entity_type' => 'qms_incident', 'entity_id' => $incident->id,
            'severity' => $incident->severity, 'type' => $incident->type,
            'created_by' => $incident->created_by, 'owner_id' => $incident->assigned_to,
        ]);

        if ($incident->assigned_to) {
            $this->notifyAssignee($incident, $u);
        }

        return redirect('/incidents/' . $incident->id)->with('ok', "Incident {$incident->reference} created.");
    }

    public function show(Request $request, string $id)
    {
        $incident = Incident::with('capas')->findOrFail($id);
        $users = $this->users();
        $evidence = Evidence::where('related_type', 'qms_incident')->where('related_id', $id)->latest()->get();
        $audit = AuditTrail::where('entity_type', 'qms_incident')->where('entity_id', $id)->orderByDesc('seq')->get();
        return view('incidents.show', compact('incident', 'users', 'evidence', 'audit'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'severity' => 'required|in:low,medium,high,critical',
            'source' => 'nullable|string|max:60',
            'root_cause' => 'nullable|string',
            'containment_action' => 'nullable|string',
            'assigned_to' => 'nullable|string|max:36',
            'due_date' => 'nullable|date',
        ]);
        $incident = Incident::findOrFail($id);
        $wasAssigned = $incident->assigned_to;
        $before = $incident->only(array_keys($data));
        $incident->update($data);

        $u = $this->user($request);
        $this->audit->record('qms_incident', $incident->id, 'edit', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['old' => $before, 'new' => $incident->only(array_keys($data))],
        ]);

        if ($incident->assigned_to && $incident->assigned_to !== $wasAssigned) {
            $this->notifyAssignee($incident, $u);
        }

        return back()->with('ok', 'Incident updated.');
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:open,investigating,capa_raised,closed',
            'reason' => 'nullable|string|max:255',
        ]);
        $incident = Incident::findOrFail($id);
        $old = $incident->status;
        $incident->update(['status' => $data['status']]);

        $u = $this->user($request);
        $this->audit->record('qms_incident', $incident->id, 'status_change', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['status' => ['old' => $old, 'new' => $incident->status]],
            'reason' => $data['reason'] ?? null,
        ]);

        if ($incident->assigned_to) {
            $this->notifier->notify($incident->assigned_to, 'status_change',
                "Incident {$incident->reference} is now {$incident->status}",
                $incident->title, 'qms_incident', $incident->id, true);
        }

        return back()->with('ok', "Incident moved to {$incident->status}.");
    }

    private function notifyAssignee(Incident $incident, array $actor): void
    {
        $this->notifier->notify($incident->assigned_to, 'assignment',
            "You were assigned incident {$incident->reference}",
            $incident->title . ($incident->due_date ? ' (due ' . $incident->due_date->format('d M Y') . ')' : ''),
            'qms_incident', $incident->id, true);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        $count = Incident::where('reference', 'like', "INC $year %")->count() + 1;
        return sprintf('INC %s %04d', $year, $count);
    }
}

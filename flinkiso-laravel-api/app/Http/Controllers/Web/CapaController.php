<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\AuditTrail;
use App\Models\Qms\Capa;
use App\Models\Qms\Evidence;
use App\Models\Qms\Incident;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\Notifier;
use App\Services\Qms\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapaController extends Controller
{
    public function __construct(
        private AuditTrailService $audit,
        private Notifier $notifier,
        private SignatureService $signatures,
    ) {}

    private function user(Request $r): array { return $r->session()->get('flink_user'); }

    private function users()
    {
        return DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name', 'username']);
    }

    public function index(Request $request)
    {
        $q = Capa::with('incident')->latest();
        if ($request->filled('status')) $q->where('status', $request->string('status'));
        $capas = $q->paginate(20)->withQueryString();
        return view('capa.index', compact('capas'));
    }

    public function create(Request $request)
    {
        $incident = $request->filled('incident_id') ? Incident::find($request->string('incident_id')) : null;
        $incidents = Incident::whereIn('status', ['open', 'investigating', 'capa_raised'])
            ->latest()->get(['id', 'reference', 'title']);
        return view('capa.create', ['users' => $this->users(), 'incident' => $incident, 'incidents' => $incidents]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:corrective,preventive',
            'incident_id' => 'nullable|string|max:36',
            'root_cause' => 'nullable|string',
            'action_plan' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|string|max:36',
            'due_date' => 'nullable|date',
        ]);
        $u = $this->user($request);
        $data['reference'] = $this->nextReference();
        $data['status'] = 'open';
        $data['created_by'] = $u['id'];

        $capa = Capa::create($data);

        $this->audit->record('qms_capa', $capa->id, 'create', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['new' => $capa->only(['reference', 'title', 'type']), 'incident_id' => $capa->incident_id],
        ]);

        // Keep the originating incident in sync: any incident still being worked
        // (open or investigating) moves to "CAPA raised" when a CAPA is raised.
        if ($capa->incident_id && ($inc = Incident::find($capa->incident_id))) {
            if (in_array($inc->status, ['open', 'investigating'], true)) {
                $from = $inc->status;
                $inc->update(['status' => 'capa_raised']);
                $this->audit->record('qms_incident', $inc->id, 'status_change', [
                    'user_id' => $u['id'], 'username' => $u['username'],
                    'reason' => "CAPA {$capa->reference} raised",
                    'changes' => ['status' => ['old' => $from, 'new' => 'capa_raised']],
                ]);
            }
        }

        if ($capa->assigned_to) { $this->notifyOwner($capa); }

        return redirect('/capa/' . $capa->id)->with('ok', "CAPA {$capa->reference} created.");
    }

    public function show(string $id)
    {
        $capa = Capa::with('incident')->findOrFail($id);
        $users = $this->users();
        $evidence = Evidence::where('related_type', 'qms_capa')->where('related_id', $id)->latest()->get();
        $audit = AuditTrail::where('entity_type', 'qms_capa')->where('entity_id', $id)->orderByDesc('seq')->get();
        return view('capa.show', compact('capa', 'users', 'evidence', 'audit'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|string|max:36',
            'due_date' => 'nullable|date',
            'root_cause' => 'nullable|string',
            'action_plan' => 'nullable|string',
        ]);
        $capa = Capa::findOrFail($id);
        $wasAssigned = $capa->assigned_to;
        $capa->update($data);
        $u = $this->user($request);
        $this->audit->record('qms_capa', $capa->id, 'edit', ['user_id' => $u['id'], 'username' => $u['username']]);
        if ($capa->assigned_to && $capa->assigned_to !== $wasAssigned) { $this->notifyOwner($capa); }
        return back()->with('ok', 'CAPA updated.');
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate(['status' => 'required|in:open,in_progress,effectiveness_check,closed,cancelled', 'reason' => 'nullable|string|max:255']);
        $capa = Capa::findOrFail($id);

        // Effectiveness must be verified before closing (client acceptance rule).
        if ($data['status'] === 'closed' && !$capa->effectiveness_verified) {
            return back()->withErrors(['status' => 'Run and confirm the effectiveness check before closing this CAPA.']);
        }
        $old = $capa->status;
        $capa->status = $data['status'];
        if ($data['status'] === 'closed') { $capa->closed_at = now(); }
        $capa->save();

        $u = $this->user($request);
        $this->audit->record('qms_capa', $capa->id, 'status_change', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['status' => ['old' => $old, 'new' => $capa->status]], 'reason' => $data['reason'] ?? null,
        ]);
        if ($capa->assigned_to) {
            $this->notifier->notify($capa->assigned_to, 'status_change', "CAPA {$capa->reference} is now " . str_replace('_', ' ', $capa->status), $capa->title, 'qms_capa', $capa->id, true);
        }
        return back()->with('ok', "CAPA moved to " . str_replace('_', ' ', $capa->status) . '.');
    }

    /** Effectiveness check — a real 21 CFR Part 11 electronic signature (password re-auth). */
    public function verify(Request $request, string $id)
    {
        $data = $request->validate([
            'effectiveness_notes' => 'required|string',
            'verified' => 'required|boolean',
            'reason' => 'nullable|string|max:255',
            'password' => 'required|string',
        ]);
        $capa = Capa::findOrFail($id);
        $u = $this->user($request);

        // Authenticate the signer at the moment of signing (Part 11).
        if (!$this->signatures->verify($u['id'], $data['password'])) {
            return back()->withErrors(['password' => 'Your password is required and must be correct to sign the effectiveness check.'])->withInput();
        }

        $capa->update([
            'effectiveness_notes' => $data['effectiveness_notes'],
            'effectiveness_verified' => (bool) $data['verified'],
            'verified_by' => $u['id'],
            'status' => 'effectiveness_check',
        ]);
        $meaning = $data['verified'] ? 'verified' : 'rejected';
        $auditRow = $this->audit->record('qms_capa', $capa->id, 'effectiveness_check', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'reason' => $data['reason'] ?? 'effectiveness check',
            'signature_meaning' => $meaning,
            'changes' => ['effectiveness_verified' => (bool) $data['verified']],
        ]);
        // Record the electronic signature bound to this action (electronic_signatures table).
        $this->signatures->sign($capa->id, null, 'effectiveness_check', $meaning, $data['reason'] ?? 'effectiveness check', $u, $auditRow?->seq, $request->ip(), 'qms_capa');

        return back()->with('ok', 'Effectiveness check electronically signed by ' . $u['username'] . '. You can now close the CAPA.');
    }

    private function notifyOwner(Capa $capa): void
    {
        $this->notifier->notify($capa->assigned_to, 'assignment',
            "You own CAPA {$capa->reference}",
            $capa->title . ($capa->due_date ? ' (due ' . $capa->due_date->format('d M Y') . ')' : ''),
            'qms_capa', $capa->id, true);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        $count = Capa::where('reference', 'like', "CAPA $year %")->count() + 1;
        return sprintf('CAPA %s %04d', $year, $count);
    }
}

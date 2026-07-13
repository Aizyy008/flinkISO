<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\AuditTrail;
use App\Models\Qms\Capa;
use App\Models\Qms\HaccpCcp;
use App\Models\Qms\HaccpCcpLog;
use App\Models\Qms\HaccpHazard;
use App\Models\Qms\HaccpPlan;
use App\Models\Qms\HaccpStep;
use App\Models\Qms\Incident;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\Notifier;
use Illuminate\Http\Request;

/**
 * HACCP / Food Safety (ISO 22000).
 * Plans, process steps, hazard analysis, CCPs, and CCP monitoring.
 * A CCP reading outside its critical limit becomes a deviation that
 * auto raises an Incident + linked CAPA (reusing the M1.2 engine).
 */
class HaccpController extends Controller
{
    public function __construct(private AuditTrailService $audit, private Notifier $notifier) {}

    private function user(Request $r): array { return $r->session()->get('flink_user'); }

    // ---------- Plans ----------

    public function index()
    {
        $plans = HaccpPlan::withCount(['ccps', 'hazards'])->latest()->paginate(20);
        return view('haccp.index', compact('plans'));
    }

    public function create()
    {
        return view('haccp.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team' => 'nullable|string',
        ]);
        $u = $this->user($request);
        $data['reference'] = $this->nextReference();
        $data['status'] = 'draft';
        $data['created_by'] = $u['id'];

        $plan = HaccpPlan::create($data);
        $this->audit->record('qms_haccp_plan', $plan->id, 'create', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['new' => $plan->only(['reference', 'product'])],
        ]);

        return redirect('/haccp/' . $plan->id)->with('ok', "HACCP plan {$plan->reference} created.");
    }

    public function show(string $id)
    {
        $plan = HaccpPlan::with(['steps', 'hazards.step', 'ccps.step'])->findOrFail($id);
        $logs = HaccpCcpLog::whereIn('ccp_id', $plan->ccps->pluck('id'))->latest('measured_at')->limit(30)->get();
        $audit = AuditTrail::where('entity_type', 'qms_haccp_plan')->where('entity_id', $id)->orderByDesc('seq')->get();
        return view('haccp.show', compact('plan', 'logs', 'audit'));
    }

    public function transition(Request $request, string $id)
    {
        $data = $request->validate(['to' => 'required|in:draft,approved,active,obsolete']);
        $plan = HaccpPlan::findOrFail($id);
        $from = $plan->status;
        $plan->status = $data['to'];
        if ($data['to'] === 'approved') {
            $plan->approved_by = $this->user($request)['id'];
            $plan->approved_date = now()->toDateString();
        }
        $plan->save();
        $this->audit->record('qms_haccp_plan', $plan->id, 'status_change', [
            'user_id' => $this->user($request)['id'], 'username' => $this->user($request)['username'],
            'changes' => ['status' => ['old' => $from, 'new' => $plan->status]],
            'signature_meaning' => $data['to'] === 'approved' ? 'approved' : null,
        ]);
        return back()->with('ok', "Plan moved to {$plan->status}.");
    }

    // ---------- Building blocks ----------

    public function addStep(Request $request, string $id)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $plan = HaccpPlan::findOrFail($id);
        $seq = ($plan->steps()->max('seq') ?? 0) + 1;
        HaccpStep::create(['plan_id' => $plan->id, 'seq' => $seq, 'name' => $data['name'], 'description' => $data['description'] ?? null]);
        return back()->with('ok', 'Process step added.');
    }

    public function addHazard(Request $request, string $id)
    {
        $data = $request->validate([
            'step_id' => 'nullable|string|max:36',
            'hazard_type' => 'required|in:biological,chemical,physical,allergen',
            'description' => 'nullable|string',
            'significance' => 'required|in:low,medium,high',
            'control_measure' => 'nullable|string',
            'control_type' => 'required|in:PRP,OPRP,CCP',
        ]);
        $data['plan_id'] = $id;
        HaccpHazard::create($data);
        return back()->with('ok', 'Hazard added to the analysis.');
    }

    public function addCcp(Request $request, string $id)
    {
        $data = $request->validate([
            'step_id' => 'nullable|string|max:36',
            'hazard_id' => 'nullable|string|max:36',
            'name' => 'required|string|max:255',
            'critical_limit' => 'required|string|max:255',
            'limit_min' => 'nullable|numeric',
            'limit_max' => 'nullable|numeric',
            'monitor_what' => 'nullable|string|max:255',
            'monitor_how' => 'nullable|string|max:255',
            'monitor_frequency' => 'nullable|string|max:255',
            'responsible' => 'nullable|string|max:255',
            'corrective_action' => 'nullable|string',
        ]);
        $data['plan_id'] = $id;
        $ccp = HaccpCcp::create($data);
        $this->audit->record('qms_haccp_plan', $id, 'ccp_added', [
            'user_id' => $this->user($request)['id'], 'username' => $this->user($request)['username'],
            'changes' => ['ccp' => $ccp->name, 'critical_limit' => $ccp->critical_limit],
        ]);
        return back()->with('ok', "CCP '{$ccp->name}' added.");
    }

    // ---------- CCP monitoring (the deviation -> CAPA flow) ----------

    public function logCcp(Request $request, string $ccpId)
    {
        $data = $request->validate([
            'batch_no' => 'nullable|string|max:255',
            'measured_value' => 'nullable|numeric',
            'measured_time' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $ccp = HaccpCcp::with('plan')->findOrFail($ccpId);
        $u = $this->user($request);

        $within = $ccp->isWithinLimit($data['measured_value'] ?? null);
        $log = HaccpCcpLog::create([
            'ccp_id' => $ccp->id,
            'plan_id' => $ccp->plan_id,
            'batch_no' => $data['batch_no'] ?? null,
            'measured_value' => $data['measured_value'] ?? null,
            'measured_time' => $data['measured_time'] ?? null,
            'operator_id' => $u['id'],
            'within_limit' => $within,
            'result' => $within ? 'ok' : 'deviation',
            'notes' => $data['notes'] ?? null,
            'logged_by' => $u['id'],
        ]);

        if ($within) {
            return back()->with('ok', 'CCP reading logged (within limit).');
        }

        // Deviation: raise an Incident + linked CAPA (reuses the M1.2 engine).
        $desc = "CCP '{$ccp->name}' deviation. Measured {$data['measured_value']} against limit {$ccp->critical_limit}"
            . ($data['batch_no'] ?? null ? " (batch {$data['batch_no']})" : '');

        $incident = Incident::create([
            'reference' => $this->nextIncidentRef(),
            'type' => 'deviation',
            'title' => "CCP deviation: {$ccp->name} ({$ccp->plan->product})",
            'description' => $desc,
            'severity' => 'critical',
            'source' => 'ccp',
            'status' => 'capa_raised',
            'company_id' => null,
            'detected_by' => $u['id'],
            'detected_date' => now()->toDateString(),
            'created_by' => $u['id'],
        ]);

        $capa = Capa::create([
            'reference' => $this->nextCapaRef(),
            'incident_id' => $incident->id,
            'type' => 'corrective',
            'title' => "CAPA for CCP deviation: {$ccp->name}",
            'root_cause' => $desc,
            'action_plan' => $ccp->corrective_action,
            'status' => 'open',
            'priority' => 'high',
            'created_by' => $u['id'],
        ]);

        $log->update(['capa_id' => $capa->id]);

        $this->audit->record('qms_haccp_ccp_log', $log->id, 'deviation', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['measured' => $data['measured_value'] ?? null, 'limit' => $ccp->critical_limit],
            'reason' => 'CCP out of critical limit; incident + CAPA raised',
        ]);
        $this->audit->record('qms_incident', $incident->id, 'create', ['user_id' => $u['id'], 'username' => $u['username'], 'reason' => 'auto from CCP deviation']);
        $this->audit->record('qms_capa', $capa->id, 'create', ['user_id' => $u['id'], 'username' => $u['username'], 'reason' => 'auto from CCP deviation']);

        $this->notifier->notify($u['id'], 'deviation',
            "CCP deviation on {$ccp->plan->product}",
            "$desc. CAPA {$capa->reference} raised.",
            'qms_capa', $capa->id, true);

        return back()->with('ok', "Deviation recorded. Incident {$incident->reference} and CAPA {$capa->reference} raised automatically.");
    }

    // ---------- References ----------

    private function nextReference(): string
    {
        $year = date('Y');
        $count = HaccpPlan::where('reference', 'like', "HACCP $year %")->count() + 1;
        return sprintf('HACCP %s %04d', $year, $count);
    }

    private function nextIncidentRef(): string
    {
        $year = date('Y');
        $count = Incident::where('reference', 'like', "INC $year %")->count() + 1;
        return sprintf('INC %s %04d', $year, $count);
    }

    private function nextCapaRef(): string
    {
        $year = date('Y');
        $count = Capa::where('reference', 'like', "CAPA $year %")->count() + 1;
        return sprintf('CAPA %s %04d', $year, $count);
    }
}

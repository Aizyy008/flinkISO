<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Capa;
use App\Models\Qms\HaccpCcp;
use App\Models\Qms\HaccpCcpLog;
use App\Models\Qms\HaccpHazard;
use App\Models\Qms\HaccpPlan;
use App\Models\Qms\HaccpStep;
use App\Models\Qms\Incident;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * HACCP REST API (Milestone 2.2, ISO 22000). Plans, steps, hazards, CCPs and
 * CCP monitoring logs. A reading outside the critical limit automatically
 * raises a critical Incident + linked CAPA (reuses the M1.2 engine).
 */
class HaccpController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    public function index(): JsonResponse
    {
        return response()->json(HaccpPlan::withCount(['steps', 'hazards', 'ccps'])->latest()->paginate(20));
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(HaccpPlan::with(['steps', 'hazards', 'ccps.logs'])->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team' => 'nullable|string',
        ]);
        $user = $request->attributes->get('flink_user');
        $data['reference'] = $this->nextRef('HACCP', HaccpPlan::class, 'HACCP');
        $data['status'] = 'draft';
        $data['created_by'] = $user['sub'] ?? null;
        $plan = HaccpPlan::create($data);
        $this->audit->record('qms_haccp_plan', $plan->id, 'create', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
            'changes' => ['new' => $plan->only(['reference', 'product'])],
        ]);
        return response()->json($plan, 201);
    }

    public function addStep(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $plan = HaccpPlan::findOrFail($id);
        $seq = (int) $plan->steps()->max('seq') + 1;
        $step = HaccpStep::create(['plan_id' => $plan->id, 'seq' => $seq, 'name' => $data['name'], 'description' => $data['description'] ?? null]);
        return response()->json($step, 201);
    }

    public function addHazard(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'step_id' => 'nullable|string|max:36',
            'hazard_type' => 'required|in:biological,chemical,physical,allergen',
            'description' => 'nullable|string',
            'significance' => 'required|in:low,medium,high',
            'control_measure' => 'nullable|string',
            'control_type' => 'required|in:PRP,OPRP,CCP',
        ]);
        $plan = HaccpPlan::findOrFail($id);
        $hazard = HaccpHazard::create($data + ['plan_id' => $plan->id]);
        return response()->json($hazard, 201);
    }

    public function addCcp(Request $request, string $id): JsonResponse
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
            'corrective_action' => 'nullable|string',
        ]);
        $plan = HaccpPlan::findOrFail($id);
        $ccp = HaccpCcp::create($data + ['plan_id' => $plan->id]);
        return response()->json($ccp, 201);
    }

    /** Log a CCP reading; a deviation auto-raises a critical Incident + CAPA. */
    public function logCcp(Request $request, string $ccpId): JsonResponse
    {
        $data = $request->validate([
            'batch_no' => 'nullable|string|max:255',
            'measured_value' => 'nullable|numeric',
            'measured_time' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $ccp = HaccpCcp::with('plan')->findOrFail($ccpId);
        $user = $request->attributes->get('flink_user');
        $within = $ccp->isWithinLimit($data['measured_value'] ?? null);

        $log = HaccpCcpLog::create([
            'ccp_id' => $ccp->id, 'plan_id' => $ccp->plan_id,
            'batch_no' => $data['batch_no'] ?? null,
            'measured_value' => $data['measured_value'] ?? null,
            'measured_time' => $data['measured_time'] ?? null,
            'operator_id' => $user['sub'] ?? null,
            'within_limit' => $within,
            'result' => $within ? 'ok' : 'deviation',
            'notes' => $data['notes'] ?? null,
            'logged_by' => $user['sub'] ?? null,
        ]);

        if ($within) {
            return response()->json(['log' => $log, 'result' => 'ok'], 201);
        }

        $desc = "CCP '{$ccp->name}' deviation. Measured {$data['measured_value']} against limit {$ccp->critical_limit}"
            . (($data['batch_no'] ?? null) ? " (batch {$data['batch_no']})" : '');
        $incident = Incident::create([
            'reference' => $this->nextRef('INC', Incident::class, 'INC'),
            'type' => 'deviation',
            'title' => "CCP deviation: {$ccp->name} ({$ccp->plan->product})",
            'description' => $desc, 'severity' => 'critical', 'source' => 'ccp',
            'status' => 'capa_raised', 'detected_by' => $user['sub'] ?? null,
            'detected_date' => now()->toDateString(), 'created_by' => $user['sub'] ?? null,
        ]);
        $capa = Capa::create([
            'reference' => $this->nextRef('CAPA', Capa::class, 'CAPA'),
            'incident_id' => $incident->id, 'type' => 'corrective',
            'title' => "CAPA for CCP deviation: {$ccp->name}",
            'root_cause' => $desc, 'action_plan' => $ccp->corrective_action,
            'status' => 'open', 'created_by' => $user['sub'] ?? null,
        ]);

        return response()->json([
            'log' => $log, 'result' => 'deviation',
            'incident' => $incident->only(['id', 'reference', 'title']),
            'capa' => $capa->only(['id', 'reference', 'title']),
        ], 201);
    }

    /** Shared "PREFIX YYYY 0001" reference generator. */
    private function nextRef(string $prefix, string $model, string $like): string
    {
        $year = date('Y');
        return sprintf('%s %s %04d', $prefix, $year, $model::where('reference', 'like', "$like $year %")->count() + 1);
    }
}

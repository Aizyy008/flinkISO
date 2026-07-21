<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Kpi;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\WorkflowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * KPI Engine REST API (Milestone 2.2). JWT-protected. Exposes KPI definitions,
 * periodic results, and the calculated status/dashboard so external systems
 * (and the AI microservice) can read live KPI performance.
 */
class KpiController extends Controller
{
    public function __construct(private AuditTrailService $audit, private WorkflowEngine $workflows) {}

    public function index(Request $request): JsonResponse
    {
        $q = Kpi::query()->with('latestResult');
        foreach (['area', 'standard', 'related_site', 'related_department', 'related_process', 'status'] as $f) {
            if ($request->filled($f)) {
                $q->where($f, $request->string($f));
            }
        }
        return response()->json($q->orderBy('area')->orderBy('name')->paginate(20));
    }

    public function show(string $id): JsonResponse
    {
        $kpi = Kpi::with('results')->findOrFail($id);
        $latest = $kpi->results->last();
        return response()->json([
            'kpi' => $kpi,
            'current_value' => $latest?->value,
            'current_status' => $kpi->statusFor($latest?->value),
            'achievement_pct' => $kpi->achievement($latest?->value),
        ]);
    }

    /** Calculated dashboard summary — status counts computed from stored results. */
    public function dashboard(Request $request): JsonResponse
    {
        $kpis = Kpi::with('latestResult')->where('status', 'active')->get();
        $summary = ['on_target' => 0, 'warning' => 0, 'critical' => 0, 'no_data' => 0];
        $items = $kpis->map(function (Kpi $k) use (&$summary) {
            $status = $k->statusFor($k->latestResult?->value);
            $summary[$status]++;
            return [
                'id' => $k->id, 'reference' => $k->reference, 'name' => $k->name,
                'area' => $k->area, 'value' => $k->latestResult?->value,
                'target' => $k->target_value, 'status' => $status,
                'achievement_pct' => $k->achievement($k->latestResult?->value),
            ];
        });
        return response()->json(['summary' => $summary, 'kpis' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'required|in:quality,environment,safety,food_safety,info_security',
            'standard' => 'nullable|string|max:40',
            'unit' => 'nullable|string|max:40',
            'calculation_method' => 'nullable|string|max:255',
            'target_value' => 'nullable|numeric',
            'warning_threshold' => 'nullable|numeric',
            'critical_threshold' => 'nullable|numeric',
            'direction' => 'required|in:higher_better,lower_better',
            'aggregation' => 'required|in:monthly,quarterly,yearly',
            'frequency' => 'nullable|string|max:40',
            'data_source' => 'nullable|string|max:255',
            'related_process' => 'nullable|string|max:255',
            'related_site' => 'nullable|string|max:255',
            'related_department' => 'nullable|string|max:255',
            'owner_id' => 'nullable|string|max:36',
            'status' => 'nullable|in:active,inactive',
        ]);
        $user = $request->attributes->get('flink_user');
        $data['reference'] = $this->nextReference();
        $data['created_by'] = $user['sub'] ?? null;
        $kpi = Kpi::create($data);
        $this->audit->record('qms_kpi', $kpi->id, 'create', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
            'changes' => ['new' => $kpi->only(['reference', 'name', 'target_value'])],
        ]);
        return response()->json($kpi, 201);
    }

    /** Record a periodic result; a threshold breach fires the workflow engine. */
    public function storeResult(Request $request, string $id): JsonResponse
    {
        $kpi = Kpi::findOrFail($id);
        $data = $request->validate([
            'period_label' => 'required|string|max:40',
            'period_date' => 'required|date',
            'value' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);
        $user = $request->attributes->get('flink_user');
        $result = $kpi->results()->updateOrCreate(
            ['period_label' => $data['period_label']],
            ['period_date' => $data['period_date'], 'value' => $data['value'], 'notes' => $data['notes'] ?? null, 'recorded_by' => $user['sub'] ?? null]
        );
        $status = $kpi->statusFor($data['value']);
        $this->audit->record('qms_kpi', $kpi->id, 'result', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
            'changes' => ['period' => $data['period_label'], 'value' => $data['value'], 'status' => $status],
        ]);
        if ($status === 'critical' || $status === 'warning') {
            $this->workflows->dispatch('kpi.threshold_breached', [
                'entity_type' => 'qms_kpi', 'entity_id' => $kpi->id, 'kpi' => $kpi->reference,
                'value' => $data['value'], 'status' => $status, 'created_by' => $user['sub'] ?? null, 'owner_id' => $kpi->owner_id,
            ]);
        }
        return response()->json(['result' => $result, 'status' => $status], 201);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        return sprintf('KPI %s %04d', $year, Kpi::where('reference', 'like', "KPI $year %")->count() + 1);
    }
}

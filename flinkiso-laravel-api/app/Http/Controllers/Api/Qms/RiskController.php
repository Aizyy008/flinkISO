<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Risk;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\WorkflowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    public function __construct(
        private AuditTrailService $audit,
        private WorkflowEngine $workflows,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Risk::query()->latest();
        if ($request->filled('standard')) $q->where('standard', $request->string('standard'));
        if ($request->filled('status')) $q->where('status', $request->string('status'));
        return response()->json($q->paginate(20));
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(Risk::findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'standard' => 'nullable|string|max:20',
            'context' => 'nullable|string|max:60',
            'hazard_type' => 'nullable|string|max:60',
            'description' => 'nullable|string',
            'likelihood' => 'required|integer|min:1|max:5',
            'severity' => 'required|integer|min:1|max:5',
            'detection' => 'required|integer|min:1|max:5',
            'treatment_plan' => 'nullable|string',
            'owner_id' => 'nullable|string|max:36',
            'company_id' => 'nullable|string|max:36',
        ]);

        $user = $request->attributes->get('flink_user');
        $risk = new Risk($data);
        $risk->reference = $this->nextReference();
        $risk->created_by = $user['sub'] ?? null;
        $risk->recalculate();
        $risk->save();

        $this->audit->record('qms_risk', $risk->id, 'create', [
            'user_id' => $user['sub'] ?? null,
            'username' => $user['username'] ?? null,
            'changes' => ['new' => $risk->only(['reference', 'title', 'risk_score', 'risk_level'])],
        ]);

        // High/critical risks can trigger workflow rules (e.g. notify owner, raise CAPA).
        $this->workflows->dispatch('risk.assessed', [
            'entity_type' => 'qms_risk',
            'entity_id' => $risk->id,
            'risk_level' => $risk->risk_level,
            'risk_score' => $risk->risk_score,
            'owner_id' => $risk->owner_id,
            'created_by' => $risk->created_by,
        ]);

        return response()->json($risk, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'likelihood' => 'sometimes|integer|min:1|max:5',
            'severity' => 'sometimes|integer|min:1|max:5',
            'detection' => 'sometimes|integer|min:1|max:5',
            'treatment_plan' => 'nullable|string',
            'status' => 'sometimes|in:open,mitigated,accepted,closed',
            'reason' => 'nullable|string|max:255',
        ]);

        $risk = Risk::findOrFail($id);
        $before = $risk->only(['likelihood', 'severity', 'detection', 'risk_score', 'status']);
        $risk->fill($data);
        $risk->recalculate();
        $risk->save();

        $user = $request->attributes->get('flink_user');
        $this->audit->record('qms_risk', $risk->id, 'update', [
            'user_id' => $user['sub'] ?? null,
            'username' => $user['username'] ?? null,
            'changes' => ['old' => $before, 'new' => $risk->only(['likelihood', 'severity', 'detection', 'risk_score', 'status'])],
            'reason' => $data['reason'] ?? null,
        ]);

        return response()->json($risk);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        $count = Risk::where('reference', 'like', "RISK-$year-%")->count() + 1;
        return sprintf('RISK-%s-%04d', $year, $count);
    }
}

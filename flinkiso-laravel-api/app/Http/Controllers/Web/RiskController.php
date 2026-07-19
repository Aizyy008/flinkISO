<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\AuditTrail;
use App\Models\Qms\Evidence;
use App\Models\Qms\Risk;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\WorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskController extends Controller
{
    public function __construct(private AuditTrailService $audit, private WorkflowEngine $workflows) {}

    private function user(Request $r): array { return $r->session()->get('flink_user'); }

    private function refData(): array
    {
        return [
            'users' => DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name', 'username']),
            'standards' => DB::connection('flinkiso')->table('standards')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function index(Request $request)
    {
        $q = Risk::query()->latest();
        if ($request->filled('status')) $q->where('status', $request->string('status'));
        if ($request->filled('risk_level')) $q->where('risk_level', $request->string('risk_level'));
        $risks = $q->paginate(20)->withQueryString();
        return view('risks.index', compact('risks'));
    }

    public function create()
    {
        return view('risks.create', $this->refData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $u = $this->user($request);
        $risk = new Risk($data);
        $risk->reference = $this->nextReference();
        $risk->created_by = $u['id'];
        $risk->recalculate();
        $risk->save();

        $this->audit->record('qms_risk', $risk->id, 'create', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['new' => $risk->only(['reference', 'title', 'risk_score', 'risk_level'])],
        ]);

        $this->workflows->dispatch('risk.assessed', [
            'entity_type' => 'qms_risk', 'entity_id' => $risk->id,
            'risk_level' => $risk->risk_level, 'risk_score' => $risk->risk_score,
            'owner_id' => $risk->owner_id, 'created_by' => $risk->created_by,
        ]);

        return redirect('/risks/' . $risk->id)->with('ok', "Risk {$risk->reference} added (score {$risk->risk_score}, {$risk->risk_level}).");
    }

    public function show(string $id)
    {
        $risk = Risk::findOrFail($id);
        $ref = $this->refData();
        $evidence = Evidence::where('related_type', 'qms_risk')->where('related_id', $id)->latest()->get();
        $audit = AuditTrail::where('entity_type', 'qms_risk')->where('entity_id', $id)->orderByDesc('seq')->get();
        return view('risks.show', ['risk' => $risk, 'evidence' => $evidence, 'audit' => $audit] + $ref);
    }

    public function update(Request $request, string $id)
    {
        $data = $this->validated($request, false);
        $risk = Risk::findOrFail($id);
        $before = $risk->only(['likelihood', 'severity', 'detection', 'risk_score', 'status']);
        $risk->fill($data);
        $risk->recalculate();
        $risk->save();

        $u = $this->user($request);
        $this->audit->record('qms_risk', $risk->id, 'update', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['old' => $before, 'new' => $risk->only(['likelihood', 'severity', 'detection', 'risk_score', 'status'])],
        ]);
        return back()->with('ok', "Risk recalculated: score {$risk->risk_score} ({$risk->risk_level}).");
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'title' => ($creating ? 'required' : 'sometimes') . '|string|max:255',
            'standard' => 'nullable|string|max:20',
            'context' => 'nullable|string|max:60',
            'hazard_type' => 'nullable|string|max:60',
            'description' => 'nullable|string',
            'likelihood' => 'required|integer|min:1|max:5',
            'severity' => 'required|integer|min:1|max:5',
            'detection' => 'required|integer|min:1|max:5',
            'treatment_plan' => 'nullable|string',
            'owner_id' => 'nullable|string|max:36',
            'status' => 'nullable|in:open,mitigated,accepted,closed',
        ]);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        $count = Risk::where('reference', 'like', "RISK $year %")->count() + 1;
        return sprintf('RISK %s %04d', $year, $count);
    }
}

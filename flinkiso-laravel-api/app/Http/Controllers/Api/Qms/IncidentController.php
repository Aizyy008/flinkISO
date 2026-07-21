<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Incident;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\WorkflowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function __construct(
        private AuditTrailService $audit,
        private WorkflowEngine $workflows,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Incident::query()->latest();
        if ($request->filled('status')) $q->where('status', $request->string('status'));
        if ($request->filled('type')) $q->where('type', $request->string('type'));

        return response()->json($q->paginate(20));
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(Incident::with('capas')->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'in:non_conformity,deviation,incident,complaint,near_miss',
            'description' => 'nullable|string',
            'severity' => 'in:low,medium,high,critical',
            'source' => 'nullable|string|max:60',
            'company_id' => 'nullable|string|max:36',
            'iso_standard' => 'nullable|in:' . implode(',', array_keys(config('iso_overlays'))),
            'iso_overlay' => 'nullable|array',
        ]);
        if (!empty($data['iso_standard'])) {
            $allowed = array_keys(config("iso_overlays.{$data['iso_standard']}.fields", []));
            $data['iso_overlay'] = array_intersect_key($data['iso_overlay'] ?? [], array_flip($allowed)) ?: null;
        }

        $user = $request->attributes->get('flink_user');
        $data['reference'] = $this->nextReference();
        $data['created_by'] = $user['sub'] ?? null;
        $data['detected_by'] = $user['sub'] ?? null;
        $data['detected_date'] = now()->toDateString();

        $incident = Incident::create($data);

        $this->audit->record('qms_incident', $incident->id, 'create', [
            'user_id' => $user['sub'] ?? null,
            'username' => $user['username'] ?? null,
            'changes' => ['new' => $incident->only(['reference', 'title', 'type', 'severity', 'status'])],
        ]);

        // Fire workflow engine so rules (e.g. high severity -> auto-CAPA + notify) can run.
        $this->workflows->dispatch('incident.created', [
            'entity_type' => 'qms_incident',
            'entity_id' => $incident->id,
            'severity' => $incident->severity,
            'type' => $incident->type,
            'created_by' => $incident->created_by,
        ]);

        return response()->json($incident->fresh('capas'), 201);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:open,investigating,capa_raised,closed',
            'reason' => 'nullable|string|max:255',
            'root_cause' => 'nullable|string',
            'containment_action' => 'nullable|string',
        ]);

        $incident = Incident::findOrFail($id);
        $old = $incident->status;
        $incident->fill($data)->save();

        $user = $request->attributes->get('flink_user');
        $this->audit->record('qms_incident', $incident->id, 'status_change', [
            'user_id' => $user['sub'] ?? null,
            'username' => $user['username'] ?? null,
            'changes' => ['status' => ['old' => $old, 'new' => $incident->status]],
            'reason' => $data['reason'] ?? null,
        ]);

        return response()->json($incident);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        $count = Incident::where('reference', 'like', "INC $year %")->count() + 1;
        return sprintf('INC %s %04d', $year, $count);
    }
}

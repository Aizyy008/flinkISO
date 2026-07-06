<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Capa;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CapaController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $q = Capa::query()->latest();
        if ($request->filled('status')) $q->where('status', $request->string('status'));
        return response()->json($q->paginate(20));
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(Capa::with('incident')->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'in:corrective,preventive',
            'incident_id' => 'nullable|string|max:36',
            'root_cause' => 'nullable|string',
            'action_plan' => 'nullable|string',
            'priority' => 'in:low,medium,high',
            'assigned_to' => 'nullable|string|max:36',
            'due_date' => 'nullable|date',
        ]);

        $user = $request->attributes->get('flink_user');
        $data['reference'] = $this->nextReference();
        $data['created_by'] = $user['sub'] ?? null;

        $capa = Capa::create($data);

        $this->audit->record('qms_capa', $capa->id, 'create', [
            'user_id' => $user['sub'] ?? null,
            'username' => $user['username'] ?? null,
            'changes' => ['new' => $capa->only(['reference', 'title', 'type', 'status'])],
        ]);

        return response()->json($capa, 201);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:open,in_progress,effectiveness_check,closed,cancelled',
            'reason' => 'nullable|string|max:255',
        ]);

        $capa = Capa::findOrFail($id);
        $old = $capa->status;
        $capa->status = $data['status'];
        if ($data['status'] === 'closed') {
            $capa->closed_at = now();
        }
        $capa->save();

        $user = $request->attributes->get('flink_user');
        $this->audit->record('qms_capa', $capa->id, 'status_change', [
            'user_id' => $user['sub'] ?? null,
            'username' => $user['username'] ?? null,
            'changes' => ['status' => ['old' => $old, 'new' => $capa->status]],
            'reason' => $data['reason'] ?? null,
        ]);

        return response()->json($capa);
    }

    /** Record the effectiveness check with an e-signature meaning. */
    public function verifyEffectiveness(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'effectiveness_notes' => 'required|string',
            'verified' => 'required|boolean',
            'reason' => 'nullable|string|max:255',
        ]);

        $capa = Capa::findOrFail($id);
        $user = $request->attributes->get('flink_user');

        $capa->effectiveness_notes = $data['effectiveness_notes'];
        $capa->effectiveness_verified = $data['verified'];
        $capa->verified_by = $user['sub'] ?? null;
        $capa->save();

        $this->audit->record('qms_capa', $capa->id, 'sign', [
            'user_id' => $user['sub'] ?? null,
            'username' => $user['username'] ?? null,
            'reason' => $data['reason'] ?? 'effectiveness check',
            'signature_meaning' => 'verified',
            'changes' => ['effectiveness_verified' => ['new' => $capa->effectiveness_verified]],
        ]);

        return response()->json($capa);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        $count = Capa::where('reference', 'like', "CAPA-$year-%")->count() + 1;
        return sprintf('CAPA-%s-%04d', $year, $count);
    }
}

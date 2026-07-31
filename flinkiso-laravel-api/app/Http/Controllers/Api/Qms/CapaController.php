<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Capa;
use App\Models\Qms\Incident;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\SignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CapaController extends Controller
{
    public function __construct(private AuditTrailService $audit, private SignatureService $signatures) {}

    private function actor(Request $request): array
    {
        $u = $request->attributes->get('flink_user');
        return ['id' => $u['sub'] ?? null, 'username' => $u['username'] ?? null, 'name' => $u['name'] ?? null];
    }

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

        // Sync the originating incident (open or investigating -> capa_raised).
        if ($capa->incident_id && ($inc = Incident::find($capa->incident_id)) && in_array($inc->status, ['open', 'investigating'], true)) {
            $from = $inc->status;
            $inc->update(['status' => 'capa_raised']);
            $this->audit->record('qms_incident', $inc->id, 'status_change', [
                'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
                'reason' => "CAPA {$capa->reference} raised",
                'changes' => ['status' => ['old' => $from, 'new' => 'capa_raised']],
            ]);
        }

        return response()->json($capa, 201);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:open,in_progress,effectiveness_check,closed,cancelled',
            'reason' => 'nullable|string|max:255',
        ]);

        $capa = Capa::findOrFail($id);

        // Same control as the web UI: closing requires a completed effectiveness check.
        if ($data['status'] === 'closed' && !$capa->effectiveness_verified) {
            return response()->json(['error' => 'effectiveness_required', 'message' => 'The effectiveness check must be completed before closing this CAPA.'], 422);
        }
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
        $actor = $this->actor($request);

        $capa->effectiveness_notes = $data['effectiveness_notes'];
        $capa->effectiveness_verified = (bool) $data['verified'];
        $capa->verified_by = $actor['id'];
        $capa->status = 'effectiveness_check';
        $capa->save();

        $meaning = $data['verified'] ? 'verified' : 'rejected';
        $auditRow = $this->audit->record('qms_capa', $capa->id, 'effectiveness_check', [
            'user_id' => $actor['id'], 'username' => $actor['username'],
            'reason' => $data['reason'] ?? 'effectiveness check',
            'signature_meaning' => $meaning,
            'changes' => ['effectiveness_verified' => ['new' => $capa->effectiveness_verified]],
        ]);
        // Record the electronic signature (the JWT identity is the authenticated signer).
        $this->signatures->sign($capa->id, null, 'effectiveness_check', $meaning, $data['reason'] ?? 'effectiveness check', $actor, $auditRow?->seq, $request->ip(), 'qms_capa');

        return response()->json($capa);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        $count = Capa::where('reference', 'like', "CAPA $year %")->count() + 1;
        return sprintf('CAPA %s %04d', $year, $count);
    }
}

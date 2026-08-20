<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Validation;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\SignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GMP / Validation logging REST API (Milestone 2.2).
 */
class ValidationController extends Controller
{
    public function __construct(private AuditTrailService $audit, private SignatureService $signatures) {}

    public function index(Request $request): JsonResponse
    {
        $q = Validation::query()->latest();
        if ($request->filled('type')) $q->where('type', $request->string('type'));
        if ($request->filled('status')) $q->where('status', $request->string('status'));
        $page = $q->paginate(20);
        $page->getCollection()->transform(fn (Validation $v) => tap($v, fn ($v) => $v->setAttribute('revalidation_status', $v->revalidationStatus())));
        return response()->json($page);
    }

    public function show(string $id): JsonResponse
    {
        $v = Validation::with('asset')->findOrFail($id);
        $v->setAttribute('revalidation_status', $v->revalidationStatus());
        return response()->json($v);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(Validation::TYPES)),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'asset_id' => 'nullable|string|max:36',
            'protocol_no' => 'nullable|string|max:255',
            'performed_date' => 'nullable|date',
            'performed_by' => 'nullable|string|max:255',
            'result' => 'nullable|in:pass,fail,conditional',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $user = $request->attributes->get('flink_user');
        $data['reference'] = $this->nextReference();
        $data['status'] = 'in_progress';
        $data['created_by'] = $user['sub'] ?? null;
        $v = Validation::create($data);
        $this->audit->record('qms_validation', $v->id, 'create', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
            'changes' => ['new' => $v->only(['reference', 'type', 'title'])],
        ]);
        return response()->json($v, 201);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:in_progress,approved,rejected,expired',
            'password' => 'nullable|string',
        ]);
        $v = Validation::findOrFail($id);
        $user = $request->attributes->get('flink_user');
        $old = $v->status;
        $meaning = $data['status'] === 'approved' ? 'approved' : null;

        // GMP approval is a signing act — authenticate the signer (Part 11), same rule as the web UI.
        // Without this check the API could record an "approved" status and a signature_meaning of
        // "approved" without ever verifying the signer's password.
        if ($meaning && !$this->signatures->verify((string) ($user['sub'] ?? ''), $data['password'] ?? null)) {
            return response()->json([
                'message' => 'A correct password is required to approve this validation (electronic signature).',
            ], 422);
        }

        $v->update(['status' => $data['status']]);
        $auditRow = $this->audit->record('qms_validation', $v->id, 'status_change', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
            'changes' => ['status' => ['old' => $old, 'new' => $v->status]],
            'signature_meaning' => $meaning,
        ]);
        if ($meaning) {
            $this->signatures->sign(
                $v->id, null, 'approve', $meaning, 'GMP / validation approval (API)',
                ['id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null, 'name' => $user['name'] ?? null],
                $auditRow?->seq, $request->ip(), 'qms_validation'
            );
        }
        return response()->json($v);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        return sprintf('VAL %s %04d', $year, Validation::where('reference', 'like', "VAL $year %")->count() + 1);
    }
}

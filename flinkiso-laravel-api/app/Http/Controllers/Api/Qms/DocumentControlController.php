<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\ChangeRequest;
use App\Models\Qms\ControlledCopy;
use App\Models\Qms\Document;
use App\Models\Qms\DocumentVersion;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Document Control v2 governance layer.
 * Lifecycle: draft to review to approved to released to obsolete.
 * Every transition is written to the immutable audit trail with an e signature meaning.
 */
class DocumentControlController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $q = Document::query()->latest();
        if ($request->filled('status')) $q->where('status', $request->string('status'));
        if ($request->filled('category')) $q->where('category', $request->string('category'));

        return response()->json($q->paginate(20));
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(
            Document::with(['versions', 'changeRequests', 'controlledCopies'])->findOrFail($id)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'doc_number' => 'required|string|max:255|unique:qms_documents,doc_number',
            'title' => 'required|string|max:255',
            'category' => 'in:SOP,WI,Form,Policy,HACCP record',
            'owner_id' => 'nullable|string|max:36',
            'legacy_qc_document_id' => 'nullable|string|max:36',
            'change_summary' => 'nullable|string',
        ]);

        $user = $request->attributes->get('flink_user');
        $doc = Document::create([
            'doc_number' => $data['doc_number'],
            'title' => $data['title'],
            'category' => $data['category'] ?? 'SOP',
            'current_version' => 1,
            'status' => 'draft',
            'owner_id' => $data['owner_id'] ?? ($user['sub'] ?? null),
            'legacy_qc_document_id' => $data['legacy_qc_document_id'] ?? null,
            'created_by' => $user['sub'] ?? null,
        ]);

        DocumentVersion::create([
            'document_id' => $doc->id,
            'version' => 1,
            'change_summary' => $data['change_summary'] ?? 'Initial version',
            'status' => 'draft',
            'created_by' => $user['sub'] ?? null,
        ]);

        $this->log($doc, 'create', $user, ['new' => $doc->only(['doc_number', 'title', 'status'])]);

        return response()->json($doc->fresh('versions'), 201);
    }

    /** Move through the lifecycle: review / approved / released / obsolete. */
    public function transition(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|in:review,approved,released,obsolete,draft',
            'reason' => 'nullable|string|max:255',
        ]);

        $doc = Document::findOrFail($id);
        if (!$doc->canTransitionTo($data['to'])) {
            return response()->json([
                'message' => "Illegal transition from {$doc->status} to {$data['to']}",
                'allowed' => Document::TRANSITIONS[$doc->status] ?? [],
            ], 422);
        }

        $from = $doc->status;
        $doc->status = $data['to'];
        $doc->save();

        // Approve/release carry an e signature meaning (FDA 21 CFR Part 11).
        $signatureMeaning = match ($data['to']) {
            'review' => 'reviewed',
            'approved' => 'approved',
            'released' => 'authorized',
            default => null,
        };

        if ($data['to'] === 'released') {
            DocumentVersion::where('document_id', $doc->id)
                ->where('version', $doc->current_version)
                ->update(['status' => 'released']);
        }

        $user = $request->attributes->get('flink_user');
        $this->log($doc, 'status_change', $user, [
            'status' => ['old' => $from, 'new' => $doc->status],
        ], $data['reason'] ?? null, $signatureMeaning);

        return response()->json($doc);
    }

    /** Create a new draft version (supersedes the current one). */
    public function newVersion(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['change_summary' => 'required|string']);
        $doc = Document::findOrFail($id);

        DocumentVersion::where('document_id', $doc->id)
            ->where('version', $doc->current_version)
            ->update(['status' => 'superseded']);

        $newVersion = $doc->current_version + 1;
        $doc->update(['current_version' => $newVersion, 'status' => 'draft']);

        $user = $request->attributes->get('flink_user');
        DocumentVersion::create([
            'document_id' => $doc->id,
            'version' => $newVersion,
            'change_summary' => $data['change_summary'],
            'status' => 'draft',
            'created_by' => $user['sub'] ?? null,
        ]);

        $this->log($doc, 'new_version', $user, ['version' => ['new' => $newVersion]]);

        return response()->json($doc->fresh('versions'));
    }

    /** Raise a Change Request against a document. */
    public function changeRequest(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['reason' => 'required|string']);
        $doc = Document::findOrFail($id);
        $user = $request->attributes->get('flink_user');

        $year = date('Y');
        $count = ChangeRequest::where('reference', 'like', "CR $year %")->count() + 1;

        $cr = ChangeRequest::create([
            'reference' => sprintf('CR %s %04d', $year, $count),
            'document_id' => $doc->id,
            'reason' => $data['reason'],
            'status' => 'open',
            'requested_by' => $user['sub'] ?? null,
        ]);

        $this->log($doc, 'change_request', $user, ['change_request' => $cr->reference], $data['reason']);

        return response()->json($cr, 201);
    }

    /** Issue a controlled (printed) copy of a released document. */
    public function issueCopy(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'holder' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);
        $doc = Document::findOrFail($id);

        if ($doc->status !== 'released') {
            return response()->json(['message' => 'Only released documents can have controlled copies'], 422);
        }

        $user = $request->attributes->get('flink_user');
        $copy = ControlledCopy::create([
            'document_id' => $doc->id,
            'version' => $doc->current_version,
            'holder' => $data['holder'],
            'location' => $data['location'] ?? null,
            'issued_by' => $user['sub'] ?? null,
        ]);

        $this->log($doc, 'issue_copy', $user, ['controlled_copy' => $data['holder']]);

        return response()->json($copy, 201);
    }

    private function log(Document $doc, string $action, ?array $user, array $changes, ?string $reason = null, ?string $signatureMeaning = null): void
    {
        $this->audit->record('qms_document', $doc->id, $action, [
            'user_id' => $user['sub'] ?? null,
            'username' => $user['username'] ?? null,
            'changes' => $changes,
            'reason' => $reason,
            'signature_meaning' => $signatureMeaning,
        ]);
    }
}

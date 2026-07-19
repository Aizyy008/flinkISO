<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\ChangeRequest;
use App\Models\Qms\ControlledCopy;
use App\Models\Qms\Document;
use App\Models\Qms\DocumentVersion;
use App\Models\Qms\AuditTrail;
use App\Models\Qms\ElectronicSignature;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\SignatureService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Document Control v2 web UI (server rendered Blade).
 * Shares the same models, state machine and audit trail as the REST API.
 */
class DocumentController extends Controller
{
    public function __construct(private AuditTrailService $audit, private SignatureService $signatures) {}

    /**
     * Transitions that require an electronic signature (per the client's
     * requirement: signatures are connected to the approval and release actions).
     * Submit-for-review and obsolete are ordinary transitions, still audited.
     */
    private const SIGNING_MEANINGS = [
        'approved' => 'approved',
        'released' => 'authorized',
    ];

    private function user(Request $r): array
    {
        return $r->session()->get('flink_user');
    }

    /** Reference data (read from the legacy FlinkISO tables) for the form dropdowns. */
    private function refData(): array
    {
        return [
            'standards' => DB::connection('flinkiso')->table('standards')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name']),
            'users' => DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name', 'username']),
        ];
    }

    private const RULES = [
        'title' => 'required|string|max:255',
        'category' => 'required|in:SOP,WI,Form,Policy,HACCP record',
        'document_type' => 'nullable|string|max:60',
        'issue_number' => 'nullable|integer|min:1',
        'review_due_date' => 'nullable|date',
        'reviewer_id' => 'nullable|string|max:36',
        'approver_id' => 'nullable|string|max:36',
        'related_standard_id' => 'nullable|string|max:36',
        'related_clause_id' => 'nullable|string|max:36',
        'related_process' => 'nullable|string|max:255',
        'related_site' => 'nullable|string|max:255',
        'related_department' => 'nullable|string|max:255',
    ];

    public function index()
    {
        $documents = Document::latest()->paginate(20);
        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create', $this->refData());
    }

    public function store(Request $request)
    {
        $data = $request->validate(self::RULES + [
            'doc_number' => 'required|string|max:255|unique:qms_documents,doc_number',
            'change_summary' => 'nullable|string',
        ]);
        $u = $this->user($request);

        $doc = Document::create(collect($data)->except('change_summary')->toArray() + [
            'current_version' => 1,
            'issue_number' => $data['issue_number'] ?? 1,
            'revision_number' => 0,
            'status' => 'draft',
            'owner_id' => $u['id'],
            'created_by' => $u['id'],
        ]);
        DocumentVersion::create([
            'document_id' => $doc->id,
            'version' => 1,
            'change_summary' => $data['change_summary'] ?? 'Initial version',
            'status' => 'draft',
            'created_by' => $u['id'],
        ]);
        $this->log($doc, 'create', $u, ['new' => $doc->only(['doc_number', 'title', 'status'])]);

        return redirect('/documents/' . $doc->id)->with('ok', 'Document created as draft.');
    }

    public function show(Request $request, string $id)
    {
        $document = Document::with(['versions' => fn ($q) => $q->orderByDesc('version'), 'changeRequests', 'controlledCopies'])
            ->findOrFail($id);
        $audit = AuditTrail::where('entity_type', 'qms_document')->where('entity_id', $id)->orderByDesc('seq')->get();
        $signatures = ElectronicSignature::where('entity_id', $id)->orderByDesc('signed_at')->get();
        return view('documents.show', ['document' => $document, 'audit' => $audit, 'signatures' => $signatures] + $this->refData());
    }

    public function transition(Request $request, string $id)
    {
        $data = $request->validate([
            'to' => 'required|in:review,approved,released,obsolete,draft',
            'reason' => 'nullable|string|max:255',
            'password' => 'nullable|string',
        ]);
        $doc = Document::findOrFail($id);
        if (!$doc->canTransitionTo($data['to'])) {
            return back()->withErrors(['to' => "Illegal transition from {$doc->status} to {$data['to']}"]);
        }

        $u = $this->user($request);
        $meaning = self::SIGNING_MEANINGS[$data['to']] ?? null;

        // A signing transition requires authentication at the moment of signing (Part 11).
        if ($meaning) {
            if (!$this->signatures->verify($u['id'], $data['password'] ?? null)) {
                return back()->withErrors(['password' => 'Electronic signature failed: your password is required and must be correct to sign this action.'])->withInput();
            }
        }

        $from = $doc->status;
        $doc->status = $data['to'];

        // On release: the current version takes effect; stamp the effective date and a review date if unset.
        if ($data['to'] === 'released') {
            $doc->effective_date = now()->toDateString();
            if (!$doc->review_due_date) {
                $doc->review_due_date = now()->addYear()->toDateString();
            }
            DocumentVersion::where('document_id', $doc->id)->where('version', $doc->current_version)->update(['status' => 'released']);
        }
        $doc->save();

        $auditRow = $this->log($doc, 'status_change', $u, ['status' => ['old' => $from, 'new' => $doc->status]], $data['reason'] ?? null, $meaning);

        // Record the electronic signature bound to this exact action.
        if ($meaning) {
            $this->signatures->sign($doc->id, $doc->current_version, $data['to'], $meaning, $data['reason'] ?? null, $u, $auditRow?->seq, $request->ip());
        }

        return back()->with('ok', "Document moved to {$doc->status}." . ($meaning ? " Electronically signed as '{$meaning}' by {$u['username']}." : ''));
    }

    public function newVersion(Request $request, string $id)
    {
        $data = $request->validate(['change_summary' => 'required|string']);
        $doc = Document::findOrFail($id);
        $this->bumpVersion($doc, $data['change_summary'], $this->user($request)['id']);
        $this->log($doc, 'new_version', $this->user($request), ['version' => ['new' => $doc->current_version], 'revision' => $doc->revision_number]);

        return back()->with('ok', "New version v{$doc->current_version} (Rev {$doc->revision_number}) created, back to draft.");
    }

    /** Edit document metadata. Logged as an 'edit' audit event. */
    public function editMeta(Request $request, string $id)
    {
        $data = $request->validate(self::RULES);
        $doc = Document::findOrFail($id);
        if (in_array($doc->status, ['released', 'obsolete'], true)) {
            return back()->withErrors(['title' => 'Released or obsolete documents cannot be edited. Raise a change request and create a new version.']);
        }
        $before = $doc->only(array_keys(self::RULES));
        $doc->update($data);

        $this->log($doc, 'edit', $this->user($request), ['old' => $before, 'new' => $doc->only(array_keys(self::RULES))]);

        return back()->with('ok', 'Document details updated.');
    }

    public function changeRequest(Request $request, string $id)
    {
        $data = $request->validate(['reason' => 'required|string']);
        $doc = Document::findOrFail($id);
        $year = date('Y');
        $count = ChangeRequest::where('reference', 'like', "CR $year %")->count() + 1;
        $cr = ChangeRequest::create([
            'reference' => sprintf('CR %s %04d', $year, $count),
            'document_id' => $doc->id, 'reason' => $data['reason'], 'status' => 'open',
            'requested_by' => $this->user($request)['id'],
        ]);
        $this->log($doc, 'change_request', $this->user($request), ['change_request' => $cr->reference], $data['reason']);

        return back()->with('ok', "Change request {$cr->reference} raised (pending approval).");
    }

    /** Approve or reject a change request (CR form + approval, per the brief). */
    public function decideChangeRequest(Request $request, string $id, string $cr)
    {
        $data = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'reason' => 'nullable|string|max:255',
            'password' => 'nullable|string',
        ]);
        $doc = Document::findOrFail($id);
        $changeRequest = ChangeRequest::where('document_id', $id)->where('id', $cr)->firstOrFail();
        if ($changeRequest->status !== 'open') {
            return back()->withErrors(['decision' => 'This change request has already been decided.']);
        }
        $u = $this->user($request);

        // Approving a change request is a signing act — authenticate the signer.
        if ($data['decision'] === 'approved' && !$this->signatures->verify($u['id'], $data['password'] ?? null)) {
            return back()->withErrors(['password' => 'Your password is required to sign the approval of this change request.'])->withInput();
        }

        $changeRequest->update(['status' => $data['decision'], 'decided_by' => $u['id']]);
        $meaning = $data['decision'] === 'approved' ? 'approved' : null;
        $auditRow = $this->log($doc, 'change_request_' . $data['decision'], $u,
            ['change_request' => $changeRequest->reference], $data['reason'] ?? null, $meaning);

        if ($data['decision'] === 'approved') {
            $this->signatures->sign($doc->id, $doc->current_version, 'change_request_approved', 'approved',
                'CR ' . $changeRequest->reference . ($data['reason'] ? ': ' . $data['reason'] : ''), $u, $auditRow?->seq, $request->ip());
        }

        return back()->with('ok', "Change request {$changeRequest->reference} {$data['decision']}.");
    }

    /** Implement an approved change request: increments the version (new revision). */
    public function implementChangeRequest(Request $request, string $id, string $cr)
    {
        $doc = Document::findOrFail($id);
        $changeRequest = ChangeRequest::where('document_id', $id)->where('id', $cr)->firstOrFail();
        if ($changeRequest->status !== 'approved') {
            return back()->withErrors(['implement' => 'Only an approved change request can be implemented.']);
        }
        $this->bumpVersion($doc, 'Change request ' . $changeRequest->reference . ': ' . $changeRequest->reason, $this->user($request)['id']);
        $changeRequest->update(['status' => 'implemented']);

        $this->log($doc, 'new_version', $this->user($request), [
            'version' => ['new' => $doc->current_version], 'change_request' => $changeRequest->reference,
        ], 'Implemented ' . $changeRequest->reference);

        return back()->with('ok', "Change request {$changeRequest->reference} implemented. Document is now v{$doc->current_version} (Rev {$doc->revision_number}, draft).");
    }

    public function issueCopy(Request $request, string $id)
    {
        $data = $request->validate(['holder' => 'required|string|max:255', 'location' => 'nullable|string|max:255']);
        $doc = Document::findOrFail($id);
        if ($doc->status !== 'released') {
            return back()->withErrors(['holder' => 'Only released documents can have controlled copies']);
        }
        ControlledCopy::create([
            'document_id' => $doc->id, 'version' => $doc->current_version,
            'holder' => $data['holder'], 'location' => $data['location'] ?? null,
            'issued_by' => $this->user($request)['id'],
        ]);
        $this->log($doc, 'issue_copy', $this->user($request), ['controlled_copy' => $data['holder']]);

        return back()->with('ok', "Controlled copy issued to {$data['holder']}.");
    }

    public function pdf(string $id)
    {
        $document = Document::with(['versions' => fn ($q) => $q->orderByDesc('version'), 'controlledCopies'])->findOrFail($id);
        $standard = $document->related_standard_id
            ? DB::connection('flinkiso')->table('standards')->where('id', $document->related_standard_id)->value('name') : null;
        // Approval/release metadata from the dedicated electronic-signature records.
        $signatures = ElectronicSignature::where('entity_id', $id)->orderBy('signed_at')->get();
        $pdf = Pdf::loadView('documents.pdf', compact('document', 'signatures', 'standard'));
        return $pdf->download($document->doc_number . '-v' . $document->current_version . '-rev' . ($document->revision_number ?? 0) . '.pdf');
    }

    /** Withdraw (return) a controlled copy — keeps the register accurate. */
    public function withdrawCopy(Request $request, string $id, string $copyId)
    {
        $doc = Document::findOrFail($id);
        $copy = ControlledCopy::where('document_id', $id)->where('id', $copyId)->firstOrFail();
        if ($copy->returned_at) {
            return back()->withErrors(['copy' => 'This controlled copy is already withdrawn.']);
        }
        $copy->update(['returned_at' => now()]);
        $this->log($doc, 'withdraw_copy', $this->user($request), ['controlled_copy' => $copy->holder]);

        return back()->with('ok', "Controlled copy to {$copy->holder} withdrawn.");
    }

    /** Supersede the current version, create a new draft version, bump revision number. */
    private function bumpVersion(Document $doc, string $summary, ?string $userId): void
    {
        DocumentVersion::where('document_id', $doc->id)->where('version', $doc->current_version)->update(['status' => 'superseded']);
        $v = $doc->current_version + 1;
        $doc->update([
            'current_version' => $v,
            'revision_number' => ($doc->revision_number ?? 0) + 1,
            'status' => 'draft',
        ]);
        DocumentVersion::create([
            'document_id' => $doc->id, 'version' => $v, 'change_summary' => $summary,
            'status' => 'draft', 'created_by' => $userId,
        ]);
    }

    private function log(Document $doc, string $action, array $user, array $changes, ?string $reason = null, ?string $meaning = null)
    {
        return $this->audit->record('qms_document', $doc->id, $action, [
            'user_id' => $user['id'], 'username' => $user['username'],
            'changes' => $changes, 'reason' => $reason, 'signature_meaning' => $meaning,
        ]);
    }
}

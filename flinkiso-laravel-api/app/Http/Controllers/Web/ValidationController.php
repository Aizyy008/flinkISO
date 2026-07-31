<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Asset;
use App\Models\Qms\Validation;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\SignatureService;
use Illuminate\Http\Request;

/**
 * GMP / Validation logging (Milestone 2.2). Web UI for recording and approving
 * equipment/process/cleaning/computer-system validations with revalidation dates.
 */
class ValidationController extends Controller
{
    public function __construct(private AuditTrailService $audit, private SignatureService $signatures) {}

    private function user(Request $r): array { return $r->session()->get('flink_user'); }

    public function index(Request $request)
    {
        $q = Validation::with('asset')->latest();
        if ($request->filled('type')) $q->where('type', $request->string('type'));
        if ($request->filled('status')) $q->where('status', $request->string('status'));
        return view('validations.index', ['validations' => $q->paginate(20)->withQueryString()]);
    }

    public function create()
    {
        return view('validations.create', ['assets' => Asset::orderBy('name')->get()]);
    }

    public function store(Request $request)
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
        $u = $this->user($request);
        $data['reference'] = $this->nextReference();
        $data['status'] = 'in_progress';
        $data['created_by'] = $u['id'];
        $v = Validation::create($data);
        $this->audit->record('qms_validation', $v->id, 'create', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['new' => $v->only(['reference', 'type', 'title'])],
        ]);
        return redirect('/validations/' . $v->id)->with('ok', "Validation {$v->reference} created.");
    }

    public function show(string $id)
    {
        return view('validations.show', ['v' => Validation::with('asset')->findOrFail($id)]);
    }

    /** Approve or reject a validation record (GMP sign-off). */
    public function transition(Request $request, string $id)
    {
        $data = $request->validate([
            'to' => 'required|in:in_progress,approved,rejected,expired',
            'password' => 'nullable|string',
        ]);
        $v = Validation::findOrFail($id);
        $u = $this->user($request);
        $meaning = $data['to'] === 'approved' ? 'approved' : null;

        // GMP approval is a signing act — authenticate the signer (Part 11).
        if ($meaning && !$this->signatures->verify($u['id'], $data['password'] ?? null)) {
            return back()->withErrors(['password' => 'Your password is required and must be correct to sign this validation approval.'])->withInput();
        }

        $from = $v->status;
        $v->update(['status' => $data['to']]);
        $auditRow = $this->audit->record('qms_validation', $v->id, 'status_change', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['status' => ['old' => $from, 'new' => $v->status]],
            'signature_meaning' => $meaning,
        ]);
        if ($meaning) {
            $this->signatures->sign($v->id, null, 'approve', $meaning, 'GMP / validation approval', $u, $auditRow?->seq, $request->ip(), 'qms_validation');
        }
        return back()->with('ok', "Validation moved to {$v->status}." . ($meaning ? " Electronically signed by {$u['username']}." : ''));
    }

    private function nextReference(): string
    {
        $year = date('Y');
        return sprintf('VAL %s %04d', $year, Validation::where('reference', 'like', "VAL $year %")->count() + 1);
    }
}

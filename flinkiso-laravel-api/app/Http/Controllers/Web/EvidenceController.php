<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Evidence;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Shared evidence upload/download, used by Incidents, CAPA and Risks.
 * Files are stored privately and streamed through an authenticated route.
 */
class EvidenceController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    /** Attach a file (or note) to any QMS record. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'related_type' => 'required|string|max:100',
            'related_id' => 'required|string|max:36',
            'evidence_type' => 'required|in:file,photo,measurement,record,report',
            'title' => 'nullable|string|max:255',
            'file' => 'nullable|file|max:10240',      // 10 MB
            'note' => 'nullable|string',
            'redirect' => 'required|string',
        ]);
        $u = $request->session()->get('flink_user');

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('evidence');
        }
        if (!$path && empty($data['note'])) {
            return back()->withErrors(['file' => 'Attach a file or enter a note.']);
        }

        $evidence = Evidence::create([
            'related_type' => $data['related_type'],
            'related_id' => $data['related_id'],
            'evidence_type' => $data['evidence_type'],
            'title' => ($data['title'] ?? null) ?: ($request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'Note'),
            'file_path' => $path,
            'json_data' => !empty($data['note'] ?? null) ? ['note' => $data['note']] : null,
            'created_by' => $u['id'],
        ]);

        $this->audit->record($data['related_type'], $data['related_id'], 'evidence_added', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['evidence' => $evidence->title],
        ]);

        return redirect($data['redirect'])->with('ok', 'Evidence attached.');
    }

    /** Stream a stored evidence file (authenticated). */
    public function download(string $id)
    {
        $evidence = Evidence::findOrFail($id);
        abort_unless($evidence->file_path && Storage::exists($evidence->file_path), 404);

        return Storage::download($evidence->file_path, $evidence->title);
    }
}

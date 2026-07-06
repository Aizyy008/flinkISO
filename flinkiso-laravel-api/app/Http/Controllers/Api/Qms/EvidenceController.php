<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Evidence;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvidenceController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    /** List evidence for a given entity: /qms/evidence?related_type=qms_incident&related_id=... */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'related_type' => 'required|string|max:100',
            'related_id' => 'required|string|max:36',
        ]);

        return response()->json(
            Evidence::where('related_type', $data['related_type'])
                ->where('related_id', $data['related_id'])
                ->latest()->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'related_type' => 'required|string|max:100',
            'related_id' => 'required|string|max:36',
            'evidence_type' => 'in:file,photo,measurement,record,report',
            'title' => 'nullable|string|max:255',
            'file_path' => 'nullable|string',
            'json_data' => 'nullable|array',
        ]);

        $user = $request->attributes->get('flink_user');
        $data['created_by'] = $user['sub'] ?? null;

        $evidence = Evidence::create($data);

        $this->audit->record('qms_evidence', $evidence->id, 'create', [
            'user_id' => $user['sub'] ?? null,
            'username' => $user['username'] ?? null,
            'changes' => ['new' => $evidence->only(['related_type', 'related_id', 'evidence_type', 'title'])],
        ]);

        return response()->json($evidence, 201);
    }
}

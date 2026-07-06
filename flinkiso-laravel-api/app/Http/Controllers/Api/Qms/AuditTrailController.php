<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\AuditTrail;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    /** List audit records, optionally filtered by entity. */
    public function index(Request $request): JsonResponse
    {
        $q = AuditTrail::query()->orderByDesc('seq');
        if ($request->filled('entity_type')) $q->where('entity_type', $request->string('entity_type'));
        if ($request->filled('entity_id')) $q->where('entity_id', $request->string('entity_id'));

        return response()->json($q->paginate(50));
    }

    /** Verify the tamper-evident hash chain (FDA 21 CFR Part 11 integrity check). */
    public function verify(): JsonResponse
    {
        return response()->json($this->audit->verifyChain());
    }
}

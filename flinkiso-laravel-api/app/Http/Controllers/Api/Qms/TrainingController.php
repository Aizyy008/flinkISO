<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Training;
use App\Models\Qms\TrainingRecord;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Training & Competency REST API (Milestone 2.2). Courses, assignments and
 * completion with automatic expiry from the course validity period.
 */
class TrainingController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    public function index(): JsonResponse
    {
        return response()->json(Training::withCount('records')->orderBy('title')->paginate(20));
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(Training::with('records')->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:60',
            'validity_months' => 'nullable|integer|min:1',
            'mandatory' => 'nullable|boolean',
        ]);
        $user = $request->attributes->get('flink_user');
        $data['reference'] = $this->nextReference();
        $data['created_by'] = $user['sub'] ?? null;
        $training = Training::create($data);
        $this->audit->record('qms_training', $training->id, 'create', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
            'changes' => ['new' => $training->only(['reference', 'title'])],
        ]);
        return response()->json($training, 201);
    }

    public function assign(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['user_id' => 'required|string|max:36']);
        $training = Training::findOrFail($id);
        $user = $request->attributes->get('flink_user');
        $record = TrainingRecord::updateOrCreate(
            ['training_id' => $training->id, 'user_id' => $data['user_id'], 'status' => 'assigned'],
            ['created_by' => $user['sub'] ?? null]
        );
        $this->audit->record('qms_training', $training->id, 'assign', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
        ]);
        return response()->json($record, 201);
    }

    public function complete(Request $request, string $recordId): JsonResponse
    {
        $data = $request->validate([
            'completed_date' => 'required|date',
            'result' => 'nullable|string|max:255',
        ]);
        $record = TrainingRecord::findOrFail($recordId);
        $training = Training::findOrFail($record->training_id);
        $completed = Carbon::parse($data['completed_date']);
        $expiry = $training->validity_months ? $completed->copy()->addMonths((int) $training->validity_months) : null;
        $record->update([
            'status' => 'completed',
            'completed_date' => $completed->toDateString(),
            'expiry_date' => $expiry?->toDateString(),
            'result' => $data['result'] ?? 'Completed',
        ]);
        $user = $request->attributes->get('flink_user');
        $this->audit->record('qms_training', $training->id, 'complete', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
            'changes' => ['user' => $record->user_id, 'expiry' => $expiry?->toDateString()],
        ]);
        return response()->json($record->fresh());
    }

    private function nextReference(): string
    {
        $year = date('Y');
        return sprintf('TRN %s %04d', $year, Training::where('reference', 'like', "TRN $year %")->count() + 1);
    }
}

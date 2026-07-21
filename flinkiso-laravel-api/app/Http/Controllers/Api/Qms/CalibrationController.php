<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Asset;
use App\Models\Qms\Calibration;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Asset & Calibration REST API (Milestone 2.2). Assets with calibration
 * schedules and logs; each read exposes the computed due/overdue status.
 */
class CalibrationController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $assets = Asset::orderBy('name')->paginate(20);
        $assets->getCollection()->transform(fn (Asset $a) => tap($a, fn ($a) => $a->setAttribute('calibration_status', $a->calibrationStatus())));
        return response()->json($assets);
    }

    public function show(string $id): JsonResponse
    {
        $asset = Asset::with('calibrations')->findOrFail($id);
        $asset->setAttribute('calibration_status', $asset->calibrationStatus());
        return response()->json($asset);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'asset_type' => 'nullable|string|max:60',
            'location' => 'nullable|string|max:255',
            'serial_no' => 'nullable|string|max:255',
            'requires_calibration' => 'nullable|boolean',
            'calibration_frequency_months' => 'nullable|integer|min:1',
            'next_due_date' => 'nullable|date',
        ]);
        $user = $request->attributes->get('flink_user');
        $data['reference'] = $this->nextReference();
        $data['status'] = 'active';
        $data['created_by'] = $user['sub'] ?? null;
        $asset = Asset::create($data);
        $this->audit->record('qms_asset', $asset->id, 'create', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
            'changes' => ['new' => $asset->only(['reference', 'name'])],
        ]);
        return response()->json($asset, 201);
    }

    public function record(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'performed_date' => 'required|date',
            'result' => 'required|in:pass,fail',
            'performed_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $asset = Asset::findOrFail($id);
        $performed = Carbon::parse($data['performed_date']);
        $nextDue = $asset->calibration_frequency_months
            ? $performed->copy()->addMonths((int) $asset->calibration_frequency_months) : null;
        $user = $request->attributes->get('flink_user');
        $cal = Calibration::create([
            'asset_id' => $asset->id,
            'performed_date' => $performed->toDateString(),
            'result' => $data['result'],
            'performed_by' => $data['performed_by'] ?? null,
            'next_due_date' => $nextDue?->toDateString(),
            'notes' => $data['notes'] ?? null,
            'created_by' => $user['sub'] ?? null,
        ]);
        $asset->update(['next_due_date' => $nextDue?->toDateString()]);
        $this->audit->record('qms_asset', $asset->id, 'calibration', [
            'user_id' => $user['sub'] ?? null, 'username' => $user['username'] ?? null,
            'changes' => ['result' => $data['result'], 'next_due' => $nextDue?->toDateString()],
        ]);
        return response()->json(['calibration' => $cal, 'asset_status' => $asset->fresh()->calibrationStatus()], 201);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        return sprintf('AST %s %04d', $year, Asset::where('reference', 'like', "AST $year %")->count() + 1);
    }
}

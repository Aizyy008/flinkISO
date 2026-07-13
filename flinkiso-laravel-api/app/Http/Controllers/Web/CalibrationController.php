<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Asset;
use App\Models\Qms\Calibration;
use App\Services\Qms\AuditTrailService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalibrationController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    private function user(Request $r): array { return $r->session()->get('flink_user'); }

    public function index(Request $request)
    {
        $q = Asset::query()->orderBy('next_due_date');
        if ($request->string('filter') == 'due') {
            $q->whereNotNull('next_due_date')->whereDate('next_due_date', '<=', now()->addDays(30));
        }
        $assets = $q->paginate(20)->withQueryString();
        return view('calibration.index', compact('assets'));
    }

    public function create()
    {
        return view('calibration.create');
    }

    public function store(Request $request)
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
        $u = $this->user($request);
        $data['reference'] = $this->nextReference();
        $data['requires_calibration'] = (bool) ($data['requires_calibration'] ?? false);
        $data['status'] = 'active';
        $data['created_by'] = $u['id'];
        $asset = Asset::create($data);
        $this->audit->record('qms_asset', $asset->id, 'create', ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['new' => $asset->only(['reference', 'name'])]]);
        return redirect('/assets/' . $asset->id)->with('ok', "Asset {$asset->reference} added.");
    }

    public function show(string $id)
    {
        $asset = Asset::with('calibrations')->findOrFail($id);
        return view('calibration.show', compact('asset'));
    }

    /** Record a calibration; recomputes the next due date from the frequency. */
    public function record(Request $request, string $id)
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
            ? $performed->copy()->addMonths($asset->calibration_frequency_months) : null;

        Calibration::create([
            'asset_id' => $asset->id,
            'performed_date' => $performed->toDateString(),
            'result' => $data['result'],
            'performed_by' => $data['performed_by'] ?? null,
            'next_due_date' => $nextDue?->toDateString(),
            'notes' => $data['notes'] ?? null,
            'created_by' => $this->user($request)['id'],
        ]);
        $asset->update(['next_due_date' => $nextDue?->toDateString()]);

        $u = $this->user($request);
        $this->audit->record('qms_asset', $asset->id, 'calibration', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['result' => $data['result'], 'next_due' => $nextDue?->toDateString()],
        ]);
        return back()->with('ok', 'Calibration recorded.' . ($nextDue ? " Next due {$nextDue->format('d M Y')}." : ''));
    }

    private function nextReference(): string
    {
        $year = date('Y');
        $count = Asset::where('reference', 'like', "AST $year %")->count() + 1;
        return sprintf('AST %s %04d', $year, $count);
    }
}

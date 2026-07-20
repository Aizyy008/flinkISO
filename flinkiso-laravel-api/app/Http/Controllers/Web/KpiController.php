<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\SyncKpiToZaiKpi;
use App\Models\Qms\Kpi;
use App\Services\Integration\ZaiKpiClient;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\WorkflowEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * KPI Engine (Project 1 M3 / FlickISO §E). KPI definitions with targets and
 * thresholds, periodic result entry, and a dashboard whose status/gauges are
 * calculated from stored results (not static). Filterable by area/standard/site/
 * department/process.
 */
class KpiController extends Controller
{
    public function __construct(private AuditTrailService $audit, private WorkflowEngine $workflows) {}

    private function user(Request $r): array { return $r->session()->get('flink_user'); }

    private function refData(): array
    {
        return [
            'users' => DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name', 'username']),
            'standards' => DB::connection('flinkiso')->table('standards')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name']),
        ];
    }

    public const AREAS = ['quality' => 'Quality', 'environment' => 'Environment', 'safety' => 'Safety', 'food_safety' => 'Food safety', 'info_security' => 'Information security'];

    /** Apply the shared filters (area/standard/site/department/process). */
    private function applyFilters(Request $request, $q)
    {
        foreach (['area', 'standard', 'related_site', 'related_department', 'related_process', 'status'] as $f) {
            if ($request->filled($f)) {
                $q->where($f, $request->string($f));
            }
        }
        return $q;
    }

    public function index(Request $request)
    {
        $q = $this->applyFilters($request, Kpi::with('latestResult'));
        $kpis = $q->orderBy('area')->orderBy('name')->paginate(20)->withQueryString();
        return view('kpi.index', ['kpis' => $kpis] + $this->refData());
    }

    /** Calculated dashboard — gauges + status from stored results. */
    public function dashboard(Request $request)
    {
        $kpis = $this->applyFilters($request, Kpi::with(['latestResult', 'results']))
            ->where('status', 'active')->orderBy('area')->orderBy('name')->get();

        $summary = ['on_target' => 0, 'warning' => 0, 'critical' => 0, 'no_data' => 0];
        foreach ($kpis as $kpi) {
            $summary[$kpi->statusFor($kpi->latestResult?->value)]++;
        }
        return view('kpi.dashboard', ['kpis' => $kpis, 'summary' => $summary] + $this->refData());
    }

    public function create()
    {
        return view('kpi.create', $this->refData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $u = $this->user($request);
        $data['reference'] = $this->nextReference();
        $data['created_by'] = $u['id'];
        $kpi = Kpi::create($data);
        $this->audit->record('qms_kpi', $kpi->id, 'create', ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['new' => $kpi->only(['reference', 'name', 'target_value'])]]);
        $this->pushToZaiKpi($kpi);
        return redirect('/kpi/' . $kpi->id)->with('ok', "KPI {$kpi->reference} defined.");
    }

    public function show(string $id)
    {
        $kpi = Kpi::with('results')->findOrFail($id);
        return view('kpi.show', ['kpi' => $kpi] + $this->refData());
    }

    public function update(Request $request, string $id)
    {
        $kpi = Kpi::findOrFail($id);
        $kpi->update($this->validated($request));
        $this->audit->record('qms_kpi', $kpi->id, 'update', ['user_id' => $this->user($request)['id'], 'username' => $this->user($request)['username'], 'changes' => ['name' => $kpi->name]]);
        $this->pushToZaiKpi($kpi);
        return back()->with('ok', 'KPI updated.');
    }

    /**
     * Push a KPI to ZaiKPI (queued). Wrapped so an integration hiccup never
     * breaks the KPI save — under the sync queue driver the job runs inline.
     */
    private function pushToZaiKpi(Kpi $kpi): void
    {
        if (! config('zaikpi.enabled')) {
            return;
        }
        try {
            SyncKpiToZaiKpi::dispatch($kpi->id, (string) Str::uuid());
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Manual "Sync to ZaiKPI" from the KPI screen — immediate, with feedback. */
    public function sync(Request $request, string $id, ZaiKpiClient $client)
    {
        $kpi = Kpi::findOrFail($id);
        if (! $client->enabled()) {
            return back()->with('error', 'ZaiKPI sync is not configured for this environment.');
        }
        $u = $this->user($request);
        $res = $client->syncAndPersist($kpi, (string) Str::uuid());
        $this->audit->record('qms_kpi', $kpi->id, 'zaikpi_sync', ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => [
            'action' => $res['action'] ?? null, 'ok' => $res['ok'], 'status' => $res['status'] ?? null, 'correlation_id' => $res['correlation_id'] ?? null,
        ]]);

        return $res['ok']
            ? back()->with('ok', "KPI {$res['action']} in ZaiKPI (ref {$kpi->reference}).")
            : back()->with('error', 'ZaiKPI sync failed: ' . ($res['error'] ?? 'unknown') . '.');
    }

    /** Record a periodic result; a threshold breach fires the workflow engine. */
    public function storeResult(Request $request, string $id)
    {
        $kpi = Kpi::findOrFail($id);
        $data = $request->validate([
            'period_label' => 'required|string|max:40',
            'period_date' => 'required|date',
            'value' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);
        $u = $this->user($request);
        $result = $kpi->results()->updateOrCreate(
            ['period_label' => $data['period_label']],
            ['period_date' => $data['period_date'], 'value' => $data['value'], 'notes' => $data['notes'] ?? null, 'recorded_by' => $u['id']]
        );
        $status = $kpi->statusFor($data['value']);
        $this->audit->record('qms_kpi', $kpi->id, 'result', ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['period' => $data['period_label'], 'value' => $data['value'], 'status' => $status]]);

        if ($status === 'critical' || $status === 'warning') {
            $this->workflows->dispatch('kpi.threshold_breached', [
                'entity_type' => 'qms_kpi', 'entity_id' => $kpi->id, 'kpi' => $kpi->reference,
                'value' => $data['value'], 'status' => $status, 'created_by' => $u['id'], 'owner_id' => $kpi->owner_id,
            ]);
        }
        return back()->with('ok', "Result for {$data['period_label']} recorded — status: " . str_replace('_', ' ', $status) . '.');
    }

    /** Periodic KPI report (PDF). */
    public function report(Request $request)
    {
        $kpis = $this->applyFilters($request, Kpi::with(['latestResult', 'results']))->orderBy('area')->get();
        $pdf = Pdf::loadView('kpi.pdf', ['kpis' => $kpis, 'generated' => now()]);
        return $pdf->download('kpi-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'required|in:' . implode(',', array_keys(self::AREAS)),
            'standard' => 'nullable|string|max:40',
            'unit' => 'nullable|string|max:40',
            'calculation_method' => 'nullable|string|max:255',
            'target_value' => 'nullable|numeric',
            'warning_threshold' => 'nullable|numeric',
            'critical_threshold' => 'nullable|numeric',
            'direction' => 'required|in:higher_better,lower_better',
            'aggregation' => 'required|in:monthly,quarterly,yearly',
            'frequency' => 'nullable|string|max:40',
            'data_source' => 'nullable|string|max:255',
            'related_process' => 'nullable|string|max:255',
            'related_site' => 'nullable|string|max:255',
            'related_department' => 'nullable|string|max:255',
            'owner_id' => 'nullable|string|max:36',
            'status' => 'nullable|in:active,inactive',
        ]);
    }

    private function nextReference(): string
    {
        $year = date('Y');
        return sprintf('KPI %s %04d', $year, Kpi::where('reference', 'like', "KPI $year %")->count() + 1);
    }
}

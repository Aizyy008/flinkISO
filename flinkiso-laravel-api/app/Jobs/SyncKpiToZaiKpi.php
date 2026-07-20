<?php

namespace App\Jobs;

use App\Models\Qms\Kpi;
use App\Services\Integration\ZaiKpiClient;
use App\Services\Qms\AuditTrailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued push of a FlinkISO KPI to ZaiKPI (Developer Instructions §2.2 "queued
 * synchronization jobs", §24 #9 "failed jobs can be retried"). Persists the
 * sync outcome on the KPI and writes an immutable audit record. A failure
 * rethrows so the queue retries with back-off; on the `sync` driver the
 * dispatching controller wraps the call so a KPI save is never broken.
 */
class SyncKpiToZaiKpi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** @var array<int,int> back-off seconds between retries */
    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(public string $kpiId, public ?string $correlationId = null) {}

    public function handle(ZaiKpiClient $client, AuditTrailService $audit): void
    {
        if (! $client->enabled()) {
            return;
        }
        $kpi = Kpi::find($this->kpiId);
        if (! $kpi) {
            return;
        }

        $res = $client->syncAndPersist($kpi, $this->correlationId);

        $audit->record('qms_kpi', $kpi->id, 'zaikpi_sync', ['changes' => [
            'action' => $res['action'] ?? null,
            'ok' => $res['ok'],
            'status' => $res['status'] ?? null,
            'correlation_id' => $res['correlation_id'] ?? $this->correlationId,
        ]]);

        if (! $res['ok']) {
            // Rethrow → queue retries with back-off (no-op under the sync driver,
            // where the controller has already caught it).
            throw new \RuntimeException('ZaiKPI sync failed: ' . ($res['error'] ?? 'unknown'));
        }
    }
}

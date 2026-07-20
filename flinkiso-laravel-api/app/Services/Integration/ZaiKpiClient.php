<?php

namespace App\Services\Integration;

use App\Http\Controllers\Web\KpiController;
use App\Models\Qms\Kpi;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * FlinkISO Expansion → ZaiKPI REST API client (Developer Instructions §2.2,
 * deliverable #8 "FlinkISO Expansion API client"). Pushes a KPI master
 * definition and its date-effective target into ZaiKPI over HTTPS + bearer
 * token, idempotently on the KPI's own UUID so a re-sync updates rather than
 * duplicates (§24 acceptance #1 & #2). FlinkISO remains the system of record;
 * no cross-database writes are used.
 */
class ZaiKpiClient
{
    public function __construct(
        private string $baseUrl,
        private string $token,
        private int $timeout = 10,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            rtrim((string) config('zaikpi.base_url'), '/'),
            (string) config('zaikpi.token'),
            (int) config('zaikpi.timeout', 10),
        );
    }

    /** Outbound sync only runs when configured — safe no-op otherwise. */
    public function enabled(): bool
    {
        return (bool) config('zaikpi.enabled') && $this->baseUrl !== '' && $this->token !== '';
    }

    private function http(string $correlationId): PendingRequest
    {
        return Http::baseUrl($this->baseUrl . '/api/v1')
            ->timeout($this->timeout)
            ->withToken($this->token)               // secret never logged (spec §15)
            ->acceptJson()->asJson()
            ->withHeaders(['X-Correlation-ID' => $correlationId])
            ->retry(3, 250, throw: false);          // transient-error retries (§2.2)
    }

    /** Connection test used by the health check / manual verify. */
    public function ping(): array
    {
        $r = $this->http((string) Str::uuid())->get('ping');
        return ['ok' => $r->successful(), 'status' => $r->status(), 'body' => $r->json()];
    }

    /** Map a FlinkISO KPI onto the ZaiKPI KPI-definition payload (§4). */
    public function definitionPayload(Kpi $kpi): array
    {
        $areas = KpiController::AREAS;
        $payload = [
            'uuid' => $kpi->id,                         // external UUID (§4.1) — never a numeric id
            'kpi_code' => $kpi->reference,
            'name' => $kpi->name,
            'description' => $kpi->description,
            'category' => $areas[$kpi->area] ?? $kpi->area,
            'unit' => $kpi->unit,
            'calculation_formula' => $kpi->calculation_method,
            'aggregation_method' => $kpi->aggregation,
            'reporting_period' => $kpi->aggregation,
            'measurement_frequency' => $kpi->frequency,
            'data_source' => $kpi->data_source,
            'direction_of_improvement' => in_array($kpi->direction, ['higher_better', 'lower_better'], true) ? $kpi->direction : null,
            'status' => $kpi->status === 'inactive' ? 'inactive' : 'active',
        ];
        // Only send owner as an external UUID when it genuinely is one (§4.1).
        if ($kpi->owner_id && Str::isUuid((string) $kpi->owner_id)) {
            $payload['owner_external_uuid'] = $kpi->owner_id;
        }
        // ISO traceability (§4.2). FlinkISO stores these as text, but ZaiKPI links
        // require a UUID — so derive a deterministic UUID per value (stable across
        // re-syncs) and carry the human name in the label.
        $links = [];
        foreach (['standard' => $kpi->standard, 'process' => $kpi->related_process,
                  'department' => $kpi->related_department, 'site' => $kpi->related_site] as $type => $value) {
            if ($value) {
                $links[] = [
                    'link_type' => $type,
                    'external_uuid' => Uuid::uuid5(Uuid::NAMESPACE_URL, "flinkiso:$type:$value")->toString(),
                    'label' => (string) $value,
                ];
            }
        }
        if ($links) {
            $payload['iso_links'] = $links;
        }
        return array_filter($payload, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Create or update the KPI in ZaiKPI without duplicating (§24 #1/#2).
     * Update-first, create-on-404 → the KPI's UUID is the canonical key.
     */
    public function syncKpi(Kpi $kpi, ?string $correlationId = null): array
    {
        $correlationId ??= (string) Str::uuid();
        $payload = $this->definitionPayload($kpi);

        $put = $this->http($correlationId)->put("kpis/{$kpi->id}", $payload);

        if ($put->status() === 404) {
            $res = $this->http($correlationId)
                ->withHeaders(['Idempotency-Key' => "kpi:{$kpi->id}:create"])
                ->post('kpis', $payload);
            $action = 'created';
        } else {
            $res = $put;
            $action = 'updated';
        }

        $this->log('sync_kpi', $kpi, $res, $correlationId, $action);

        if (! $res->successful()) {
            return [
                'ok' => false, 'action' => $action, 'status' => $res->status(),
                'error' => $res->json('error.message') ?? $res->json('message') ?? 'request_failed',
                'correlation_id' => $correlationId,
            ];
        }

        // Push the current target/thresholds as a date-effective target (§6.3).
        $target = $this->pushTarget($kpi, $correlationId);

        return [
            'ok' => true, 'action' => $action, 'status' => $res->status(),
            'target' => $target, 'correlation_id' => $correlationId,
        ];
    }

    /** Push the KPI's target + thresholds as a date-effective target row (§6.3). */
    public function pushTarget(Kpi $kpi, string $correlationId): array
    {
        if ($kpi->target_value === null && $kpi->warning_threshold === null && $kpi->critical_threshold === null) {
            return ['ok' => true, 'skipped' => true];
        }
        // Stable per value-set so an unchanged re-sync is de-duplicated, while a
        // real target change creates a new date-effective row.
        $sig = md5(implode('|', [$kpi->target_value, $kpi->warning_threshold, $kpi->critical_threshold]));
        $res = $this->http($correlationId)
            ->withHeaders(['Idempotency-Key' => "kpi:{$kpi->id}:target:{$sig}"])
            ->post("kpis/{$kpi->id}/targets", array_filter([
                'target_value' => $kpi->target_value,
                'warning_threshold' => $kpi->warning_threshold,
                'critical_threshold' => $kpi->critical_threshold,
                'effective_from' => now()->toDateString(),
            ], fn ($v) => $v !== null));

        $this->log('push_target', $kpi, $res, $correlationId, 'target');
        return ['ok' => $res->successful(), 'status' => $res->status()];
    }

    /**
     * Sync a KPI and persist the outbound sync state onto the row. Never throws
     * — returns a result the caller (job or controller) can act on.
     */
    public function syncAndPersist(Kpi $kpi, ?string $correlationId = null): array
    {
        try {
            $res = $this->syncKpi($kpi, $correlationId);
        } catch (\Throwable $e) {
            report($e);
            $res = ['ok' => false, 'action' => null, 'status' => null, 'error' => 'connection_failed', 'correlation_id' => $correlationId];
        }

        $kpi->forceFill([
            'zaikpi_synced_at' => $res['ok'] ? now() : $kpi->zaikpi_synced_at,
            'zaikpi_status' => $res['ok'] ? 'synced' : 'failed',
            'zaikpi_error' => $res['ok'] ? null : Str::limit((string) ($res['error'] ?? 'sync failed'), 480),
        ])->save();

        return $res;
    }

    private function log(string $op, Kpi $kpi, $res, string $correlationId, string $action): void
    {
        Log::info("[zaikpi] {$op}", [
            'kpi' => $kpi->reference, 'uuid' => $kpi->id, 'action' => $action,
            'status' => $res->status(), 'ok' => $res->successful(),
            'correlation_id' => $correlationId,
            // Bearer token and payload secrets are intentionally excluded (§15).
        ]);
    }
}

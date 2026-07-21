<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\HaccpCcp;
use App\Models\Qms\Incident;
use App\Models\Qms\Kpi;
use App\Services\Ai\AiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AI microservice endpoints (Milestone 2.2). Each pulls real QMS record data
 * and forwards it to the FastAPI service for scoring/forecasting/suggestions.
 */
class AiController extends Controller
{
    public function __construct(private AiClient $ai) {}

    private function guard(): ?JsonResponse
    {
        return $this->ai->enabled() ? null : response()->json(['error' => 'AI service is not enabled.'], 503);
    }

    public function health(): JsonResponse
    {
        return response()->json($this->ai->health());
    }

    /** Risk score from raw FMEA inputs. */
    public function riskScore(Request $request): JsonResponse
    {
        if ($g = $this->guard()) return $g;
        $d = $request->validate([
            'likelihood' => 'required|integer|min:1|max:5',
            'severity' => 'required|integer|min:1|max:5',
            'detection' => 'required|integer|min:1|max:5',
            'context' => 'nullable|string',
        ]);
        return response()->json($this->ai->riskScore($d['likelihood'], $d['severity'], $d['detection'], $d['context'] ?? null));
    }

    /** Predictive KPI — forecast the next period from this KPI's stored results. */
    public function kpiForecast(string $id): JsonResponse
    {
        if ($g = $this->guard()) return $g;
        $kpi = Kpi::with('results')->findOrFail($id);
        $history = $kpi->results->map(fn ($r) => ['period' => $r->period_label, 'value' => (float) $r->value])->values()->all();
        $res = $this->ai->kpiForecast($history, $kpi->target_value !== null ? (float) $kpi->target_value : null, $kpi->direction);
        return response()->json(['kpi' => $kpi->only(['id', 'reference', 'name', 'target_value', 'direction']), 'ai' => $res]);
    }

    /** CAPA suggestions for an incident/non-conformity. */
    public function capaSuggest(string $id): JsonResponse
    {
        if ($g = $this->guard()) return $g;
        $inc = Incident::findOrFail($id);
        $res = $this->ai->capaSuggest($inc->title, (string) $inc->description, (string) $inc->type, (string) $inc->severity);
        return response()->json(['incident' => $inc->only(['id', 'reference', 'title']), 'ai' => $res]);
    }

    /** HACCP anomaly detection over a CCP's monitoring logs. */
    public function haccpAnomaly(string $ccpId): JsonResponse
    {
        if ($g = $this->guard()) return $g;
        $ccp = HaccpCcp::with('logs')->findOrFail($ccpId);
        $readings = $ccp->logs->map(fn ($l) => ['value' => $l->measured_value !== null ? (float) $l->measured_value : null, 'time' => $l->measured_time])->values()->all();
        $res = $this->ai->haccpAnomaly($readings, $ccp->limit_min !== null ? (float) $ccp->limit_min : null, $ccp->limit_max !== null ? (float) $ccp->limit_max : null);
        return response()->json(['ccp' => $ccp->only(['id', 'name', 'critical_limit']), 'ai' => $res]);
    }
}

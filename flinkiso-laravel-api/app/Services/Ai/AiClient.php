<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Client for the FlinkISO AI microservice (Milestone 2.2). Thin HTTP wrapper;
 * all analytics live in the Python service. Returns an ['ok'=>bool,...] shape.
 */
class AiClient
{
    public function __construct(private string $baseUrl, private string $token, private int $timeout = 35) {}

    public static function fromConfig(): self
    {
        return new self(rtrim((string) config('ai.base_url'), '/'), (string) config('ai.token'), (int) config('ai.timeout', 35));
    }

    public function enabled(): bool
    {
        return (bool) config('ai.enabled') && $this->baseUrl !== '';
    }

    private function http(): PendingRequest
    {
        $r = Http::baseUrl($this->baseUrl)->timeout($this->timeout)->acceptJson()->asJson();
        return $this->token ? $r->withToken($this->token) : $r;
    }

    private function post(string $path, array $body): array
    {
        try {
            $res = $this->http()->post($path, $body);
            return ['ok' => $res->successful(), 'status' => $res->status(), 'data' => $res->json()];
        } catch (\Throwable $e) {
            report($e);
            return ['ok' => false, 'status' => 0, 'error' => 'ai_service_unreachable'];
        }
    }

    public function health(): array
    {
        try {
            $res = Http::baseUrl($this->baseUrl)->timeout(5)->acceptJson()->get('/health');
            return ['ok' => $res->successful(), 'data' => $res->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'unreachable'];
        }
    }

    public function riskScore(int $likelihood, int $severity, int $detection, ?string $context = null): array
    {
        return $this->post('/ai/risk-score', array_filter([
            'likelihood' => $likelihood, 'severity' => $severity, 'detection' => $detection, 'context' => $context,
        ], fn ($v) => $v !== null));
    }

    public function kpiForecast(array $history, ?float $target, string $direction): array
    {
        return $this->post('/ai/kpi-forecast', ['history' => $history, 'target' => $target, 'direction' => $direction]);
    }

    public function capaSuggest(string $title, string $description, string $type, string $severity): array
    {
        return $this->post('/ai/capa-suggest', compact('title', 'description', 'type', 'severity'));
    }

    public function haccpAnomaly(array $readings, ?float $limitMin, ?float $limitMax): array
    {
        return $this->post('/ai/haccp-anomaly', ['readings' => $readings, 'limit_min' => $limitMin, 'limit_max' => $limitMax]);
    }
}

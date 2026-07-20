<?php

/*
|--------------------------------------------------------------------------
| ZaiKPI integration (outbound)
|--------------------------------------------------------------------------
| FlinkISO Expansion is the canonical system of record. When a KPI is
| defined or changed here it is pushed to ZaiKPI through its versioned REST
| API (Developer Instructions §2 / §24 acceptance #1–#2). No direct database
| writes between the apps — communication is HTTPS + bearer token only.
*/

return [
    // Master switch. When false, no outbound calls are made (safe default).
    'enabled' => env('ZAIKPI_SYNC_ENABLED', false),

    // ZaiKPI base URL, e.g. https://kpi.dctrd.us  (the client appends /api/v1).
    'base_url' => env('ZAIKPI_BASE_URL', ''),

    // Sanctum token issued in ZaiKPI for this environment, with the scopes
    // kpis:write, targets:write, kpis:read. Never hard-code — env only.
    'token' => env('ZAIKPI_API_TOKEN', ''),

    // Per-request timeout (seconds).
    'timeout' => (int) env('ZAIKPI_TIMEOUT', 10),
];

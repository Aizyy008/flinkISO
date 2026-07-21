<?php

/*
|--------------------------------------------------------------------------
| AI microservice (Milestone 2.2)
|--------------------------------------------------------------------------
| FlinkISO calls the FastAPI AI service for risk scoring, KPI forecasting,
| CAPA suggestions and HACCP anomaly detection. Disabled by default.
*/

return [
    'enabled' => env('AI_SERVICE_ENABLED', false),
    'base_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8100'),
    'token' => env('AI_SERVICE_TOKEN', ''),
    'timeout' => (int) env('AI_SERVICE_TIMEOUT', 35),
];

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\Qms\AiController;
use App\Http\Controllers\Api\Qms\AuditTrailController;
use App\Http\Controllers\Api\Qms\CalibrationController;
use App\Http\Controllers\Api\Qms\CapaController;
use App\Http\Controllers\Api\Qms\DocumentControlController;
use App\Http\Controllers\Api\Qms\EvidenceController;
use App\Http\Controllers\Api\Qms\HaccpController;
use App\Http\Controllers\Api\Qms\IncidentController;
use App\Http\Controllers\Api\Qms\KpiController;
use App\Http\Controllers\Api\Qms\NotificationController;
use App\Http\Controllers\Api\Qms\RiskController;
use App\Http\Controllers\Api\Qms\TrainingController;
use App\Http\Controllers\Api\Qms\ValidationController;
use App\Http\Controllers\Api\Qms\WorkflowController;
use Illuminate\Support\Facades\Route;

/*
| FlinkISO Laravel API
| Shares the FlinkISO MySQL DB with the legacy CakePHP app.
*/

// Public
Route::get('/health', [HealthController::class, 'health']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected by the JWT auth bridge
Route::middleware('jwt')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/legacy/standards', [HealthController::class, 'standards']);

    // ---- Milestone 1: Core QMS Engine ----
    Route::prefix('qms')->group(function () {

        // Document Control v2
        Route::get('/documents', [DocumentControlController::class, 'index']);
        Route::post('/documents', [DocumentControlController::class, 'store']);
        Route::get('/documents/{id}', [DocumentControlController::class, 'show']);
        Route::patch('/documents/{id}/transition', [DocumentControlController::class, 'transition']);
        Route::post('/documents/{id}/version', [DocumentControlController::class, 'newVersion']);
        Route::post('/documents/{id}/change-request', [DocumentControlController::class, 'changeRequest']);
        Route::post('/documents/{id}/copy', [DocumentControlController::class, 'issueCopy']);

        // Incidents / Non-conformities
        Route::get('/incidents', [IncidentController::class, 'index']);
        Route::post('/incidents', [IncidentController::class, 'store']);
        Route::get('/incidents/{id}', [IncidentController::class, 'show']);
        Route::patch('/incidents/{id}/status', [IncidentController::class, 'updateStatus']);

        // CAPA
        Route::get('/capa', [CapaController::class, 'index']);
        Route::post('/capa', [CapaController::class, 'store']);
        Route::get('/capa/{id}', [CapaController::class, 'show']);
        Route::patch('/capa/{id}/status', [CapaController::class, 'updateStatus']);
        Route::post('/capa/{id}/verify', [CapaController::class, 'verifyEffectiveness']);

        // Risks
        Route::get('/risks', [RiskController::class, 'index']);
        Route::post('/risks', [RiskController::class, 'store']);
        Route::get('/risks/{id}', [RiskController::class, 'show']);
        Route::patch('/risks/{id}', [RiskController::class, 'update']);

        // Evidence
        Route::get('/evidence', [EvidenceController::class, 'index']);
        Route::post('/evidence', [EvidenceController::class, 'store']);

        // Workflows
        Route::get('/workflows', [WorkflowController::class, 'index']);
        Route::post('/workflows', [WorkflowController::class, 'store']);
        Route::get('/workflows/{id}/runs', [WorkflowController::class, 'runs']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);

        // Audit trail (FDA 21 CFR Part 11)
        Route::get('/audit-trail', [AuditTrailController::class, 'index']);
        Route::get('/audit-trail/verify', [AuditTrailController::class, 'verify']);

        // ---- Milestone 2.1 modules exposed over the API (M2.2) ----

        // KPI Engine
        Route::get('/kpis', [KpiController::class, 'index']);
        Route::post('/kpis', [KpiController::class, 'store']);
        Route::get('/kpis/dashboard', [KpiController::class, 'dashboard']);
        Route::get('/kpis/{id}', [KpiController::class, 'show']);
        Route::post('/kpis/{id}/results', [KpiController::class, 'storeResult']);

        // Training & Competency
        Route::get('/trainings', [TrainingController::class, 'index']);
        Route::post('/trainings', [TrainingController::class, 'store']);
        Route::get('/trainings/{id}', [TrainingController::class, 'show']);
        Route::post('/trainings/{id}/assign', [TrainingController::class, 'assign']);
        Route::post('/training-records/{recordId}/complete', [TrainingController::class, 'complete']);

        // Assets & Calibration
        Route::get('/assets', [CalibrationController::class, 'index']);
        Route::post('/assets', [CalibrationController::class, 'store']);
        Route::get('/assets/{id}', [CalibrationController::class, 'show']);
        Route::post('/assets/{id}/calibrations', [CalibrationController::class, 'record']);

        // GMP / Validation logs
        Route::get('/validations', [ValidationController::class, 'index']);
        Route::post('/validations', [ValidationController::class, 'store']);
        Route::get('/validations/{id}', [ValidationController::class, 'show']);
        Route::patch('/validations/{id}/status', [ValidationController::class, 'updateStatus']);

        // AI microservice (M2.2): risk / KPI forecast / CAPA suggest / HACCP anomaly
        Route::get('/ai/health', [AiController::class, 'health']);
        Route::post('/ai/risk-score', [AiController::class, 'riskScore']);
        Route::post('/kpis/{id}/forecast', [AiController::class, 'kpiForecast']);
        Route::post('/incidents/{id}/capa-suggest', [AiController::class, 'capaSuggest']);
        Route::post('/haccp/ccps/{ccpId}/anomaly', [AiController::class, 'haccpAnomaly']);

        // HACCP (ISO 22000)
        Route::get('/haccp/plans', [HaccpController::class, 'index']);
        Route::post('/haccp/plans', [HaccpController::class, 'store']);
        Route::get('/haccp/plans/{id}', [HaccpController::class, 'show']);
        Route::post('/haccp/plans/{id}/steps', [HaccpController::class, 'addStep']);
        Route::post('/haccp/plans/{id}/hazards', [HaccpController::class, 'addHazard']);
        Route::post('/haccp/plans/{id}/ccps', [HaccpController::class, 'addCcp']);
        Route::post('/haccp/ccps/{ccpId}/logs', [HaccpController::class, 'logCcp']);
    });
});

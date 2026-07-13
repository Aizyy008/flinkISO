<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CapaController;
use App\Http\Controllers\Web\DocumentController;
use App\Http\Controllers\Web\EvidenceController;
use App\Http\Controllers\Web\HaccpController;
use App\Http\Controllers\Web\IncidentController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\RiskController;
use App\Http\Controllers\Web\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/documents'));

// Auth (session)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// QMS UI (session protected)
Route::middleware('webauth')->group(function () {

    // Document Control v2
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::get('/documents/create', [DocumentController::class, 'create']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::post('/documents/{id}/edit', [DocumentController::class, 'editMeta']);
    Route::get('/documents/{id}/pdf', [DocumentController::class, 'pdf']);
    Route::post('/documents/{id}/transition', [DocumentController::class, 'transition']);
    Route::post('/documents/{id}/version', [DocumentController::class, 'newVersion']);
    Route::post('/documents/{id}/change-request', [DocumentController::class, 'changeRequest']);
    Route::post('/documents/{id}/change-request/{cr}/decide', [DocumentController::class, 'decideChangeRequest']);
    Route::post('/documents/{id}/change-request/{cr}/implement', [DocumentController::class, 'implementChangeRequest']);
    Route::post('/documents/{id}/copy', [DocumentController::class, 'issueCopy']);

    // Incidents / Non-conformities
    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::get('/incidents/create', [IncidentController::class, 'create']);
    Route::post('/incidents', [IncidentController::class, 'store']);
    Route::get('/incidents/{id}', [IncidentController::class, 'show']);
    Route::post('/incidents/{id}/update', [IncidentController::class, 'update']);
    Route::post('/incidents/{id}/status', [IncidentController::class, 'updateStatus']);

    // CAPA
    Route::get('/capa', [CapaController::class, 'index']);
    Route::get('/capa/create', [CapaController::class, 'create']);
    Route::post('/capa', [CapaController::class, 'store']);
    Route::get('/capa/{id}', [CapaController::class, 'show']);
    Route::post('/capa/{id}/update', [CapaController::class, 'update']);
    Route::post('/capa/{id}/status', [CapaController::class, 'updateStatus']);
    Route::post('/capa/{id}/verify', [CapaController::class, 'verify']);

    // HACCP / Food Safety (ISO 22000)
    Route::get('/haccp', [HaccpController::class, 'index']);
    Route::get('/haccp/create', [HaccpController::class, 'create']);
    Route::post('/haccp', [HaccpController::class, 'store']);
    Route::get('/haccp/{id}', [HaccpController::class, 'show']);
    Route::post('/haccp/{id}/transition', [HaccpController::class, 'transition']);
    Route::post('/haccp/{id}/step', [HaccpController::class, 'addStep']);
    Route::post('/haccp/{id}/hazard', [HaccpController::class, 'addHazard']);
    Route::post('/haccp/{id}/ccp', [HaccpController::class, 'addCcp']);
    Route::post('/haccp/ccp/{ccpId}/log', [HaccpController::class, 'logCcp']);

    // Risk register
    Route::get('/risks', [RiskController::class, 'index']);
    Route::get('/risks/create', [RiskController::class, 'create']);
    Route::post('/risks', [RiskController::class, 'store']);
    Route::get('/risks/{id}', [RiskController::class, 'show']);
    Route::post('/risks/{id}/update', [RiskController::class, 'update']);

    // Evidence (shared)
    Route::post('/evidence', [EvidenceController::class, 'store']);
    Route::get('/evidence/{id}/download', [EvidenceController::class, 'download']);

    // Workflow rules
    Route::get('/workflows', [WorkflowController::class, 'index']);
    Route::get('/workflows/create', [WorkflowController::class, 'create']);
    Route::post('/workflows', [WorkflowController::class, 'store']);
    Route::post('/workflows/{id}/toggle', [WorkflowController::class, 'toggle']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
});

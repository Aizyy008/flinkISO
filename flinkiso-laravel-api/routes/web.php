<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/documents'));

// Auth (session)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Document Control v2 UI (session protected)
Route::middleware('webauth')->group(function () {
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
    Route::post('/documents/{id}/copy/{copyId}/withdraw', [DocumentController::class, 'withdrawCopy']);
});

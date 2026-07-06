<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

/*
| FlinkISO Laravel API — Phase 0 skeleton
| Shares the FlinkISO MySQL DB with the legacy CakePHP app.
*/

// Public
Route::get('/health', [HealthController::class, 'health']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected by the JWT auth bridge
Route::middleware('jwt')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/legacy/standards', [HealthController::class, 'standards']);
});

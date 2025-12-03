<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QRScanController;
use App\Http\Controllers\Api\LightControlController;
use App\Http\Controllers\Api\ProductionLogController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\AlertController;

// ==========================================
// PUBLIC API ROUTES (For ESP32 devices)
// ==========================================

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// Alerts for ESP32 polling
Route::get('/alerts/pending', [AlertController::class, 'getPending']);

// ==========================================
// AUTHENTICATED API ROUTES
// ==========================================
Route::middleware(['auth:sanctum'])->group(function () {

    // Current user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // QR Scan
    Route::post('/scan', [QRScanController::class, 'scan']);

    // Light Control
    Route::post('/lights/set', [LightControlController::class, 'setLight']);
    Route::get('/lights/status/{table}', [LightControlController::class, 'getStatus']);
    Route::get('/lights/alerts', [LightControlController::class, 'getActiveAlerts']);

    // Production Logs
    Route::post('/production', [ProductionLogController::class, 'store']);
    Route::get('/production/worker/{worker}', [ProductionLogController::class, 'getByWorker']);
    Route::get('/production/today', [ProductionLogController::class, 'getTodaySummary']);

    // Tables
    Route::get('/tables/status', [TableController::class, 'getStatus']);
    Route::get('/tables/{table}/info', [TableController::class, 'getInfo']);

    // Alerts
    Route::get('/alerts/active', [AlertController::class, 'getActive']);
    Route::post('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge']);
});

// ==========================================
// ADMIN ONLY API ROUTES
// ==========================================
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Add admin-only API routes here if needed
});

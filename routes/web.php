<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\TableAssignmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductionTargetController;
use App\Http\Controllers\Api\ESP32Controller;
use App\Http\Controllers\DeviceController;
use App\Http\Middleware\CheckDomain;

// ==========================================
// PUBLIC ROUTES (No authentication needed)
// ==========================================
//Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
//Route::post('/login', [LoginController::class, 'login']);

// ==========================================
// AUTHENTICATED ROUTES (All logged-in users)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard (all roles)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Tables - View only (all roles)
    // ⚠️ IMPORTANT: List route first, then wildcard routes
    Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
    Route::get('/tables/{table}/qr-download', [TableController::class, 'downloadQR'])->name('tables.qr-download');
    // ❌ REMOVED: Route::get('/tables/{table}', ...) - moved below admin create route

    // Workers - View only (all roles)
    Route::get('/workers', [WorkerController::class, 'index'])->name('workers.index');
    // ❌ REMOVED: Route::get('/workers/{worker}', ...) - moved below admin create route

    // Monitor page (all roles)
    Route::get('/monitor', [DashboardController::class, 'monitor'])->name('monitor');

    // Profile (all roles)
    Route::get('/profile/edit', function () {
        return view('profile.edit', ['user' => auth()->user()]);
    })->name('profile.edit');
});

// ==========================================
// ADMIN ONLY ROUTES
// ==========================================
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Tables - Create/Edit/Delete
    // ✅ CREATE route MUST come before {table} wildcard
    Route::get('/tables/create', [TableController::class, 'create'])->name('tables.create');
    Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
    Route::get('/tables/{table}/edit', [TableController::class, 'edit'])->name('tables.edit');
    Route::put('/tables/{table}', [TableController::class, 'update'])->name('tables.update');
    Route::delete('/tables/{table}', [TableController::class, 'destroy'])->name('tables.destroy');
    Route::post('/tables/{table}/regenerate-qr', [TableController::class, 'regenerateQR'])->name('tables.regenerate-qr');

    // Workers - Create/Edit/Delete
    // ✅ CREATE route MUST come before {worker} wildcard
    Route::get('/workers/create', [WorkerController::class, 'create'])->name('workers.create');
    Route::post('/workers', [WorkerController::class, 'store'])->name('workers.store');
    Route::get('/workers/{worker}/edit', [WorkerController::class, 'edit'])->name('workers.edit');
    Route::put('/workers/{worker}', [WorkerController::class, 'update'])->name('workers.update');
    Route::delete('/workers/{worker}', [WorkerController::class, 'destroy'])->name('workers.destroy');

    // Table Assignments - Full CRUD

    Route::get('/assignments/bulk', [TableAssignmentController::class, 'bulk'])
        ->name('assignments.bulk');

// Bulk Store (AJAX)
    Route::post('/assignments/bulk-store', [TableAssignmentController::class, 'bulkStore'])
        ->name('assignments.bulk-store');

// Copy Assignments (AJAX)
    Route::post('/assignments/copy', [TableAssignmentController::class, 'copy'])
        ->name('assignments.copy');

// Clear All Assignments for Date (AJAX)
    Route::post('/assignments/clear-all', [TableAssignmentController::class, 'clearAll'])
        ->name('assignments.clear-all');
    // Add before the resource route
    Route::post('/assignments/clear-date', [TableAssignmentController::class, 'clearDate'])->name('assignments.clear-date');
    Route::resource('assignments', TableAssignmentController::class);
    // User Management
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

// ==========================================
// WILDCARD SHOW ROUTES (Must come AFTER create routes)
// ==========================================
Route::middleware(['auth'])->group(function () {
    // ✅ These {table} and {worker} wildcards MUST be AFTER /create routes
    Route::get('/tables/{table}', [TableController::class, 'show'])->name('tables.show');
    Route::get('/workers/{worker}', [WorkerController::class, 'show'])->name('workers.show');
});

// ==========================================
// SUPERVISOR ONLY ROUTES
// ==========================================
Route::middleware(['auth'])->group(function () {

    // Supervisor Routes
    Route::get('/supervisor/scan', [SupervisorController::class, 'scan'])->name('supervisor.scan');
    Route::post('/supervisor/process-scan', [SupervisorController::class, 'processScan'])->name('supervisor.scan.process');  // Changed name
    Route::post('/supervisor/production', [SupervisorController::class, 'storeProduction'])->name('supervisor.production.store');
    Route::post('/supervisor/light', [SupervisorController::class, 'setLight'])->name('supervisor.light.set');
    Route::get('/supervisor/my-activity', [SupervisorController::class, 'myActivity'])->name('supervisor.my-activity');
    Route::get('/supervisor/quick-select', [SupervisorController::class, 'quickSelect'])->name('supervisor.quick-select');
    Route::get('/supervisor/device-status', [SupervisorController::class, 'getDeviceStatus'])->name('supervisor.device.status');
    
});

Route::middleware(['auth'])->group(function () {

    // Production Targets - Custom routes FIRST
    Route::get('/production-targets/bulk-create', [ProductionTargetController::class, 'bulkCreate'])->name('production-targets.bulk-create');
    Route::post('/production-targets/bulk-store', [ProductionTargetController::class, 'bulkStore'])->name('production-targets.bulk-store');
    Route::post('/production-targets/copy', [ProductionTargetController::class, 'copy'])->name('production-targets.copy');

    // Production Targets - Resource routes
    Route::resource('production-targets', ProductionTargetController::class);

});

Route::middleware(['auth'])->group(function () {
    // ... existing routes

    // Device Management
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/status', [DeviceController::class, 'getDevicesStatus'])->name('devices.status');
    Route::get('/devices/notifications', [DeviceController::class, 'getNotifications'])->name('devices.notifications');
    Route::post('/devices/notifications/{id}/read', [DeviceController::class, 'markAsRead'])->name('devices.mark-read');
    Route::post('/devices/notifications/mark-all-read', [DeviceController::class, 'markAllAsRead'])->name('devices.mark-all-read');
    Route::delete('/devices/notifications/{id}', [DeviceController::class, 'deleteNotification'])->name('devices.delete-notification');
    Route::post('/devices/notifications/clear-all', [DeviceController::class, 'clearAllNotifications'])->name('devices.clear-all');
    Route::post('/devices/command', [DeviceController::class, 'sendCommand'])->name('devices.command');
});


// ==========================================
// ADMIN & MONITOR ROUTES (Reports)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // Reports Dashboard
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Daily Report
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');

    // Monthly Report
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');

    // Worker Report
    Route::get('/reports/worker/{worker}', [ReportController::class, 'worker'])->name('reports.worker');

    // Export Reports
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

});





Route::middleware(['auth','verified', CheckDomain::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    return 'Cache cleared!';
});

require __DIR__.'/auth.php';

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
    Route::resource('assignments', TableAssignmentController::class);
    Route::post('/assignments/bulk', [TableAssignmentController::class, 'bulkAssign'])->name('assignments.bulk');
    Route::post('/assignments/copy', [TableAssignmentController::class, 'copyFromDate'])->name('assignments.copy');

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
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/scan', [SupervisorController::class, 'scan'])->name('scan');
    Route::post('/scan/process', [SupervisorController::class, 'processScan'])->name('scan.process');
    Route::post('/production/store', [SupervisorController::class, 'storeProduction'])->name('production.store');
    Route::post('/light/set', [SupervisorController::class, 'setLight'])->name('light.set');
    Route::get('/my-activity', [SupervisorController::class, 'myActivity'])->name('my-activity');
    Route::get('/quick-select', [SupervisorController::class, 'quickSelect'])->name('quick-select');
});

// ==========================================
// ADMIN & MONITOR ROUTES (Reports)
// ==========================================
Route::middleware(['auth', 'role:admin,monitor'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
    Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
    Route::get('/worker/{worker}', [ReportController::class, 'worker'])->name('worker');
    Route::post('/export', [ReportController::class, 'export'])->name('export');
});

Route::middleware(['auth','verified', CheckDomain::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

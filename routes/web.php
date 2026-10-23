<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BannerController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FeedbackController;
use App\Http\Controllers\Web\PlantController;
use App\Http\Controllers\Web\QuestionController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\PermissionController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\VisitController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\SystemConsoleController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    // Forgot Password & Reset Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:6,1');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:6,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Operations web panel: superadmin, admin, coordinator, supervisor, staff
Route::prefix('admin')->middleware(['auth', 'role:superadmin,admin,coordinator,supervisor,staff,organizer'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('shift-queue', [DashboardController::class, 'shiftQueue'])->name('shift_queue');
    Route::get('shift-queue/export-pending', [DashboardController::class, 'exportPending'])->name('shift_queue.export_pending');
    Route::get('dashboard/live-feed', [DashboardController::class, 'liveFeed'])->name('dashboard.live_feed');
    Route::get('dashboard/global-search', [DashboardController::class, 'globalSearch'])->name('dashboard.global_search');
    Route::get('dashboard/export-pending', [DashboardController::class, 'exportPending'])->name('dashboard.export_pending');

    // Account Settings: My Profile & Password Management
    Route::get('my-profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::get('myprofile', [AuthController::class, 'showProfile'])->name('myprofile');
    Route::get('change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('change-password', [AuthController::class, 'updatePassword'])->name('password.change.update');
    Route::post('profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Operations (Visits & Feedbacks) - accessible to all authorized roles
    Route::get('visits', [VisitController::class, 'index'])->name('visits.index');
    Route::get('visits/create', [VisitController::class, 'create'])->name('visits.create');
    Route::post('visits', [VisitController::class, 'store'])->name('visits.store');
    Route::get('visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
    Route::get('visits/{visit}/feedback/create', [VisitController::class, 'createFeedback'])->name('visits.feedback.create');
    Route::post('visits/{visit}/feedback', [VisitController::class, 'storeFeedback'])->name('visits.feedback.store');
    Route::post('visits/sync', [VisitController::class, 'syncExternal'])->name('visits.sync');
    Route::get('feedbacks', [FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('feedbacks/{feedback}', [FeedbackController::class, 'show'])->name('feedbacks.show');

    // Analytics & Reports - superadmin, admin, coordinator, supervisor
    Route::middleware('role:superadmin,admin,coordinator,supervisor')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // Modules accessible to coordinator, admin, and superadmin (Plant Master view, Feedback Form, Company Profile)
    Route::middleware('role:superadmin,admin,coordinator')->group(function () {
        Route::get('plants', [PlantController::class, 'index'])->name('plants.index');
        Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::get('questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::get('company', [CompanyController::class, 'edit'])->name('company.edit');
        Route::put('company', [CompanyController::class, 'update'])->name('company.update');
    });

    // Masters, User Directory & System Configuration - superadmin, admin
    Route::middleware('role:superadmin,admin')->group(function () {
        // Plant Master Management Routes
        Route::get('plants/create', [PlantController::class, 'create'])->name('plants.create');
        Route::post('plants', [PlantController::class, 'store'])->name('plants.store');
        Route::get('plants/{plant}/edit', [PlantController::class, 'edit'])->name('plants.edit');
        Route::put('plants/{plant}', [PlantController::class, 'update'])->name('plants.update');
        Route::delete('plants/{plant}', [PlantController::class, 'destroy'])->name('plants.destroy');
        Route::patch('plants/{plant}/toggle', [PlantController::class, 'toggle'])->name('plants.toggle');

        // Role Master Routes
        Route::resource('roles', RoleController::class)->except('show');
        Route::patch('roles/{role}/toggle', [RoleController::class, 'toggle'])->name('roles.toggle');

        // Permission Master Routes
        Route::resource('permissions', PermissionController::class)->except('show');

        // User Management
        Route::resource('users', UserController::class)->except('show');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        // Feedback Form Edit & Delete
        Route::get('questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
        Route::patch('questions/{question}/toggle', [QuestionController::class, 'toggle'])->name('questions.toggle');

        // Audit Logs Trail
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

        // CMS Banners Management
        Route::resource('banners', BannerController::class)->except('show');
        Route::patch('banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');

        // Database Maintenance & Table Data Cleanup
        Route::get('database-cleanup', [\App\Http\Controllers\Web\DatabaseMaintenanceController::class, 'index'])->name('database_cleanup.index');
        Route::post('database-cleanup/clear', [\App\Http\Controllers\Web\DatabaseMaintenanceController::class, 'clearTables'])->name('database_cleanup.clear');
        Route::post('database-cleanup/reseed', [\App\Http\Controllers\Web\DatabaseMaintenanceController::class, 'reseed'])->name('database_cleanup.reseed');
    });
});

// System Maintenance & Artisan Console (Direct Developer Access without auth/login)
Route::prefix('admin')->group(function () {
    Route::get('system-console', [SystemConsoleController::class, 'index'])->name('system_console.index');
    Route::post('system-console/execute', [SystemConsoleController::class, 'executeCommand'])->name('system_console.execute');
    Route::get('system/clear-cache', [SystemConsoleController::class, 'directClearCache'])->name('system_console.direct_clear_cache');
    Route::get('system/optimize', [SystemConsoleController::class, 'directOptimize'])->name('system_console.direct_optimize');
    Route::get('system/migrate', [SystemConsoleController::class, 'directMigrate'])->name('system_console.direct_migrate');
    Route::get('system/seed-daily-visits', [SystemConsoleController::class, 'directSeedDailyVisits'])->name('system_console.direct_seed_daily');
    Route::get('system/sync-external', [SystemConsoleController::class, 'directSyncExternal'])->name('system_console.direct_sync_external');
    Route::get('system/storage-link', [SystemConsoleController::class, 'directStorageLink'])->name('system_console.direct_storage_link');
    Route::get('system-console/logs', [SystemConsoleController::class, 'viewLogs'])->name('system_console.logs');
    Route::post('system-console/clear-logs', [SystemConsoleController::class, 'clearLogs'])->name('system_console.clear_logs');
    Route::get('system-console/download-logs', [SystemConsoleController::class, 'downloadLogs'])->name('system_console.download_logs');
    Route::post('system-console/maintenance-toggle', [SystemConsoleController::class, 'toggleMaintenance'])->name('system_console.maintenance_toggle');
});

// Standalone direct developer console access
Route::match(['get', 'post'], 'system-console', [SystemConsoleController::class, 'index'])->name('system_console.standalone');



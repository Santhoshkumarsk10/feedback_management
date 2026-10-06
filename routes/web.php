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
use App\Http\Controllers\Web\MobileAppController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\VisitController;
use App\Http\Controllers\Web\AuditLogController;
use Illuminate\Support\Facades\Route;

// Mobile Tablet & APK Application (Unified Visitor & Organizer)
Route::get('/app', [MobileAppController::class, 'index'])->name('mobile.app');
Route::get('/mobile', fn () => redirect()->route('mobile.app'));
Route::post('/app/feedback', [MobileAppController::class, 'submitFeedback'])->name('mobile.feedback');
Route::post('/app/organizer/login', [MobileAppController::class, 'organizerLogin'])->name('mobile.organizer.login');
Route::get('/app/organizer/visits', [MobileAppController::class, 'organizerVisits'])->name('mobile.organizer.visits');
Route::post('/app/organizer/logout', [MobileAppController::class, 'organizerLogout'])->name('mobile.organizer.logout');

Route::redirect('/', '/app');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin web panel: superadmin + admin
Route::prefix('admin')->middleware(['auth', 'role:superadmin,admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Plant Master Routes
    Route::resource('plants', PlantController::class)->except('show');
    Route::patch('plants/{plant}/toggle', [PlantController::class, 'toggle'])->name('plants.toggle');

    // Role Master Routes
    Route::resource('roles', RoleController::class)->except('show');
    Route::patch('roles/{role}/toggle', [RoleController::class, 'toggle'])->name('roles.toggle');

    // User Management
    Route::resource('users', UserController::class)->except('show');
    Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

    Route::resource('questions', QuestionController::class)->except('show');

    Route::get('visits', [VisitController::class, 'index'])->name('visits.index');

    Route::get('feedbacks', [FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('feedbacks/{feedback}', [FeedbackController::class, 'show'])->name('feedbacks.show');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Audit Logs Trail
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // Company Brand & Profile CMS
    Route::get('company', [CompanyController::class, 'edit'])->name('company.edit');
    Route::put('company', [CompanyController::class, 'update'])->name('company.update');

    // CMS Banners Management
    Route::resource('banners', BannerController::class)->except('show');
    Route::patch('banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
});

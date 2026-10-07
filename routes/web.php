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
use App\Http\Controllers\Web\ShiftController;
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
Route::get('/app/organizer/today-visitors', [MobileAppController::class, 'organizerTodayVisitors'])->name('mobile.organizer.today_visitors');
Route::post('/app/organizer/visits/{visit}/feedback', [MobileAppController::class, 'submitFeedbackOnBehalf'])->name('mobile.organizer.submit_on_behalf');
Route::post('/app/organizer/logout', [MobileAppController::class, 'organizerLogout'])->name('mobile.organizer.logout');
Route::post('/app/organizer/forgot-password', [MobileAppController::class, 'organizerForgotPassword'])->name('mobile.organizer.forgot_password');
Route::post('/app/organizer/change-password', [MobileAppController::class, 'organizerChangePassword'])->name('mobile.organizer.change_password');

Route::redirect('/', '/app');

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

// Operations web panel: superadmin, admin, supervisor, staff
Route::prefix('admin')->middleware(['auth', 'role:superadmin,admin,supervisor,staff,organizer'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Account Security: Change Password
    Route::get('change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('change-password', [AuthController::class, 'updatePassword'])->name('password.change.update');

    // Operations (Visits & Feedbacks) - accessible to all authorized roles
    Route::get('visits', [VisitController::class, 'index'])->name('visits.index');
    Route::get('visits/create', [VisitController::class, 'create'])->name('visits.create');
    Route::post('visits', [VisitController::class, 'store'])->name('visits.store');
    Route::get('visits/{visit}/feedback/create', [VisitController::class, 'createFeedback'])->name('visits.feedback.create');
    Route::post('visits/{visit}/feedback', [VisitController::class, 'storeFeedback'])->name('visits.feedback.store');
    Route::post('visits/sync', [VisitController::class, 'syncExternal'])->name('visits.sync');
    Route::get('feedbacks', [FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('feedbacks/{feedback}', [FeedbackController::class, 'show'])->name('feedbacks.show');

    // Analytics & Reports - superadmin, admin, supervisor
    Route::middleware('role:superadmin,admin,supervisor')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // Masters, User Directory & System Configuration - superadmin, admin
    Route::middleware('role:superadmin,admin')->group(function () {
        // Plant Master Routes
        Route::resource('plants', PlantController::class)->except('show');
        Route::patch('plants/{plant}/toggle', [PlantController::class, 'toggle'])->name('plants.toggle');

        // Role Master Routes
        Route::resource('roles', RoleController::class)->except('show');
        Route::patch('roles/{role}/toggle', [RoleController::class, 'toggle'])->name('roles.toggle');

        // Permission Master Routes
        Route::resource('permissions', PermissionController::class)->except('show');

        // Shift Master Routes
        Route::resource('shifts', ShiftController::class)->except('show');
        Route::patch('shifts/{shift}/toggle', [ShiftController::class, 'toggle'])->name('shifts.toggle');

        // User Management
        Route::resource('users', UserController::class)->except('show');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        Route::resource('questions', QuestionController::class)->except('show');

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
});

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\VisitorController;
use App\Http\Controllers\Api\CmsController;
use Illuminate\Support\Facades\Route;

// ---------- Visitor app: PUBLIC (no login) ----------
Route::prefix('visitor')->middleware('throttle:60,1')->group(function () {
    Route::get('/organizers', [VisitorController::class, 'organizers']);
    Route::get('/questions', [VisitorController::class, 'questions']);
    Route::get('/lookup/{visitor_id}', [VisitorController::class, 'lookup']);
    Route::post('/feedback', [VisitorController::class, 'submitFeedback'])->middleware('throttle:10,1');
});

// ---------- CMS Public Content API ----------
Route::prefix('cms')->group(function () {
    Route::get('/company', [CmsController::class, 'company']);
    Route::get('/banners', [CmsController::class, 'banners']);
});

// ---------- Organizer app: JWT ----------
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1');

Route::middleware(['auth:api', 'role:staff,organizer,admin,superadmin'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::prefix('organizer')->group(function () {
        Route::get('/dashboard', [OrganizerController::class, 'dashboard']);
        Route::get('/visits', [OrganizerController::class, 'visits']);
        Route::get('/feedback/{id}', [OrganizerController::class, 'feedbackDetail']);
        Route::post('/visits/{visit}/feedback', [OrganizerController::class, 'submitFeedbackOnBehalf']);
        Route::post('/visitors/sync', [OrganizerController::class, 'syncVisitors']);
    });
});


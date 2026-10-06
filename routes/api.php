<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\VisitorController;
use Illuminate\Support\Facades\Route;

// ---------- Visitor app: PUBLIC (no login) ----------
Route::prefix('visitor')->middleware('throttle:60,1')->group(function () {
    Route::get('/organizers', [VisitorController::class, 'organizers']);
    Route::get('/questions', [VisitorController::class, 'questions']);
    Route::post('/feedback', [VisitorController::class, 'submitFeedback'])->middleware('throttle:10,1');
});

// ---------- Organizer app: JWT ----------
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::middleware(['auth:api', 'role:organizer'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('organizer')->group(function () {
        Route::get('/dashboard', [OrganizerController::class, 'dashboard']);
        Route::get('/visits', [OrganizerController::class, 'visits']);
        Route::get('/feedback/{id}', [OrganizerController::class, 'feedbackDetail']);
    });
});

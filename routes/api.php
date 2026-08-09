<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplianceCalendarController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DeadlineController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\PagesController;
use App\Http\Controllers\Api\ResourceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ── Public reads ─────────────────────────────────────────────────────
    Route::get('/home',                 [HomeController::class, 'index']);
    Route::get('/services',             [PagesController::class, 'services']);
    Route::get('/technology',           [PagesController::class, 'technology']);
    Route::get('/compliance-calendar',  [ComplianceCalendarController::class, 'index']);
    Route::get('/resources',            [ResourceController::class, 'index']);
    Route::get('/resources/{resource}', [ResourceController::class, 'show']);
    Route::get('/news',                 [NewsController::class, 'index']);
    Route::get('/news/{news}',          [NewsController::class, 'show']);

    // ── Public writes ────────────────────────────────────────────────────
    Route::post('/contact',              [ContactController::class, 'store']);
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);

    // ── Auth (issues Sanctum tokens against public_users) ───────────────
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::post('/auth/login',    [AuthController::class, 'login'])->middleware('throttle:6,1');

    // ── Protected ─────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/account/me',   [AccountController::class, 'me']);

        Route::get('/account/deadlines',               [DeadlineController::class, 'index']);
        Route::post('/account/deadlines',               [DeadlineController::class, 'store']);
        Route::patch('/account/deadlines/{deadline}',   [DeadlineController::class, 'update']);
        Route::delete('/account/deadlines/{deadline}',  [DeadlineController::class, 'destroy']);
    });
});

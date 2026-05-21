<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\RegulatorController;
use App\Http\Controllers\Api\V1\LicenseActivityController;
use App\Http\Controllers\Api\V1\ComplianceRequirementController;
use App\Http\Controllers\Api\V1\ComplianceCalendarController;
use App\Http\Controllers\Api\V1\UserComplianceProfileController;
use App\Http\Controllers\Api\V1\FcmTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public auth endpoints
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

    // Public compliance data (read-only, no auth required for browsing)
    Route::get('regulators', [RegulatorController::class, 'index']);
    Route::get('regulators/{regulator}', [RegulatorController::class, 'show']);
    Route::get('license-activities', [LicenseActivityController::class, 'index']);
    Route::get('compliance-requirements', [ComplianceRequirementController::class, 'index']);
    Route::get('compliance-calendar', [ComplianceCalendarController::class, 'index']);

    // Authenticated endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::get('user/compliance-profiles', [UserComplianceProfileController::class, 'index']);
        Route::post('user/compliance-profiles', [UserComplianceProfileController::class, 'store']);
        Route::delete('user/compliance-profiles/{userComplianceProfile}', [UserComplianceProfileController::class, 'destroy']);

        Route::post('user/fcm-token', [FcmTokenController::class, 'store']);
    });
});

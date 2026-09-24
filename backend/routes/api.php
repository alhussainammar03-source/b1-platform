<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\ExerciseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\Api\AttemptSubmissionController;


Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post(
            '/exercises/{key}/attempts',
            [AttemptController::class, 'start']
        );

        Route::post(
            '/attempts/{attempt}/submit',
            [AttemptSubmissionController::class, 'store']
        );
    });

    Route::get('/languages', [LanguageController::class, 'index']);

    Route::get('/exercises/{key}', [ExerciseController::class, 'show']);
});

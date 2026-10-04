<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\ExerciseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\Api\AttemptSubmissionController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\WritingSubmissionController;



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

        Route::get(
            '/media/{media}/audio',
            [MediaController::class, 'audio']
        )->name('api.v1.media.audio');

        Route::post(
            '/writing/submissions',
            [WritingSubmissionController::class, 'store']
        );

        Route::patch(
            '/writing/submissions/{submission}/confirm',
            [WritingSubmissionController::class, 'confirm']
        );

        Route::post(
            '/writing/submissions/{submission}/evaluate',
            [WritingSubmissionController::class, 'evaluate']

        );


        Route::get(
            '/writing/evaluations/{evaluation}',
            [WritingSubmissionController::class, 'showEvaluation']
        );
    });


    Route::get('/languages', [LanguageController::class, 'index']);
    Route::get('/exercises', [ExerciseController::class, 'index']);
    Route::get('/exercises/{key}', [ExerciseController::class, 'show']);
});

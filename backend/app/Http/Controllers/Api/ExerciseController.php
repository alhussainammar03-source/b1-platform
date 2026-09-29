<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    public function show(string $key): ExerciseResource|JsonResponse
    {
        $exercise = Exercise::query()
            ->where('key', $key)
            ->where('status', 'published')
            ->with([
                'stimuli.mediaFile',
                'questions.answerOptions',
            ])
            ->first();

        if (! $exercise) {
            return response()->json([
                'message' => 'Exercise not found.',
            ], 404);
        }

        return new ExerciseResource($exercise);
    }
}

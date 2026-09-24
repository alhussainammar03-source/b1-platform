<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    public function start(Request $request, string $key): JsonResponse
    {
        $exercise = Exercise::query()
            ->where('key', $key)
            ->where('status', 'published')
            ->first();

        if (! $exercise) {
            return response()->json([
                'message' => 'Exercise not found.',
            ], 404);
        }

        $attempt = Attempt::create([
            'user_id' => $request->user()->id,
            'exercise_id' => $exercise->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Attempt started.',
            'data' => [
                'id' => $attempt->id,
                'exercise_id' => $attempt->exercise_id,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at,
            ],
        ], 201);
    }
}

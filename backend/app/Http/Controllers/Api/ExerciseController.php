<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class ExerciseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'section' => ['nullable', 'string', 'max:50'],
        ]);

        $exercises = Exercise::query()
            ->where('status', 'published')
            ->when(
                $validated['section'] ?? null,
                fn($query, $section) => $query->whereHas(
                    'examSection',
                    fn($sectionQuery) => $sectionQuery->where('key', $section)
                )
            )
            ->with([
                'examSection:id,key',
                'examPart:id,key,title,sort_order',
            ])
            ->orderBy('exam_part_id')
            ->orderBy('sort_order')
            ->get()
            ->map(fn(Exercise $exercise) => [
                'id' => $exercise->id,
                'key' => $exercise->key,
                'type' => $exercise->type,
                'title' => $exercise->title,
                'description' => $exercise->description,
                'difficulty' => $exercise->difficulty,
                'access_level' => $exercise->access_level,
                'sort_order' => $exercise->sort_order,

                'section' => $exercise->examSection
                    ? [
                        'key' => $exercise->examSection->key,
                    ]
                    : null,

                'part' => $exercise->examPart
                    ? [
                        'id' => $exercise->examPart->id,
                        'key' => $exercise->examPart->key,
                        'title' => $exercise->examPart->title,
                        'sort_order' => $exercise->examPart->sort_order,
                    ]
                    : null,
            ]);

        return response()->json([
            'data' => $exercises,
        ]);
    }

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

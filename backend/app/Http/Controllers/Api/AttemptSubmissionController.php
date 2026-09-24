<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAttemptRequest;
use App\Models\Attempt;
use App\Services\AttemptGradingService;
use Illuminate\Http\JsonResponse;

class AttemptSubmissionController extends Controller
{
    public function store(
        SubmitAttemptRequest $request,
        Attempt $attempt,
        AttemptGradingService $gradingService
    ): JsonResponse {
        // المستخدم يستطيع إرسال محاولاته فقط
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        // لا نسمح بتسليم المحاولة أكثر من مرة
        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'message' => 'Attempt has already been submitted.',
            ], 422);
        }

        $result = $gradingService->grade(
            $attempt,
            $request->validated('answers')
        );

        return response()->json([
            'message' => 'Attempt submitted successfully.',
            'data' => $result,
        ]);
    }
}

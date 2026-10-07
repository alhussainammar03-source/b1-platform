<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamFormat;
use App\Models\ExamPart;
use App\Models\SpeakingSession;
use App\Services\Speaking\SpeakingConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpeakingSessionController extends Controller
{
    public function store(
        Request $request,
        SpeakingConversationService $conversationService
    ): JsonResponse {
        $user = $request->user();

        $examFormat = ExamFormat::query()
            ->where('key', 'dtz')
            ->where('is_active', true)
            ->firstOrFail();

        $examPart = ExamPart::query()
            ->where('exam_format_id', $examFormat->id)
            ->where('key', 'introduction')
            ->where('is_active', true)
            ->firstOrFail();

        [$session, $greeting] = DB::transaction(
            function () use (
                $user,
                $examFormat,
                $examPart,
                $conversationService
            ) {
                $session = SpeakingSession::create([
                    'user_id' => $user->id,
                    'exam_format_id' => $examFormat->id,
                    'exam_part_id' => $examPart->id,
                    'mode' => 'practice',
                    'status' => 'in_progress',
                    'current_part' => 1,
                ]);

                $greeting = $conversationService
                    ->startPartOne($session);

                return [$session, $greeting];
            }
        );

        return response()->json([
            'session' => [
                'id' => $session->id,
                'mode' => $session->mode,
                'status' => $session->status,
                'current_part' => $session->current_part,
                'covered_topics' => $session->fresh()->covered_topics,
                'started_at' => $session->fresh()->started_at,
            ],

            'turn' => [
                'id' => $greeting->id,
                'speaker' => $greeting->speaker,
                'text' => $greeting->text,
                'turn_number' => $greeting->turn_number,
                'turn_type' => $greeting->turn_type,
                'meta' => $greeting->meta,
            ],
        ], 201);
    }
}

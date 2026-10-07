<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpeakingSession;
use App\Services\Speaking\SpeakingConversationService;
use App\Services\Speaking\SpeakingResponseAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpeakingTurnController extends Controller
{
    public function store(
        Request $request,
        SpeakingSession $session,
        SpeakingResponseAnalysisService $analysisService,
        SpeakingConversationService $conversationService
    ): JsonResponse {
        /*
         * المستخدم يستطيع إرسال إجابة فقط إلى جلسته الخاصة.
         */
        abort_unless(
            $session->user_id === $request->user()->id,
            403
        );

        /*
         * الجلسة يجب أن تكون ما زالت فعالة.
         */
        abort_unless(
            $session->status === 'in_progress',
            422
        );

        $validated = $request->validate([
            'text' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        /*
         * التحليل يتم Server-side.
         * لاحقًا هذه الخدمة ستتصل بمزود AI حقيقي.
         */
        $analysis = $analysisService->analyzePartOneText(
            $session,
            $validated['text']
        );

        /*
         * حفظ إجابة المستخدم وتحديث حالة المحادثة.
         */
        $userTurn = $conversationService->addUserAnswer(
            $session,
            $validated['text'],
            $analysis
        );

        /*
         * إنشاء رد الممتحن.
         */
        $aiTurn = $conversationService->createAiFollowUp(
            $session,
            $userTurn->analysis
        );

        $session->refresh();

        return response()->json([
            'session' => [
                'id' => $session->id,
                'status' => $session->status,
                'current_part' => $session->current_part,
                'covered_topics' => $session->covered_topics,
                'meta' => $session->meta,
            ],

            'user_turn' => [
                'id' => $userTurn->id,
                'speaker' => $userTurn->speaker,
                'text' => $userTurn->text,
                'turn_number' => $userTurn->turn_number,
                'turn_type' => $userTurn->turn_type,
            ],

            'ai_turn' => [
                'id' => $aiTurn->id,
                'speaker' => $aiTurn->speaker,
                'text' => $aiTurn->text,
                'turn_number' => $aiTurn->turn_number,
                'turn_type' => $aiTurn->turn_type,
                'meta' => $aiTurn->meta,
            ],
        ], 201);
    }
}

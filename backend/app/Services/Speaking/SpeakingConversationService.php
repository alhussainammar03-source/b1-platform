<?php

namespace App\Services\Speaking;

use App\Models\SpeakingSession;
use App\Models\SpeakingTurn;
use Illuminate\Support\Facades\DB;

class SpeakingConversationService
{
    public function startPartOne(SpeakingSession $session): SpeakingTurn
    {
        return DB::transaction(function () use ($session) {
            $session->update([
                'current_part' => 1,
                'status' => 'in_progress',
                'covered_topics' => [
                    'name' => false,
                    'age' => false,
                    'origin' => false,
                    'residence' => false,
                    'profession' => false,
                    'hobbies' => false,
                ],
                'started_at' => $session->started_at ?? now(),
            ]);

            $nextTurnNumber = ((int) $session->turns()->max('turn_number')) + 1;

            return SpeakingTurn::create([
                'speaking_session_id' => $session->id,
                'exam_part_id' => $session->exam_part_id,
                'speaker' => 'ai',
                'turn_number' => $nextTurnNumber,
                'turn_type' => 'greeting',

                'text' => implode(' ', [
                    'Hallo und herzlich willkommen zu Ihrer B1-Sprechprüfung.',
                    'Schön, dass Sie da sind.',
                    'Wir beginnen jetzt mit Teil 1.',
                    'In diesem Teil möchten wir Sie ein bisschen kennenlernen.',
                    'Bitte stellen Sie sich kurz vor.',
                    'Erzählen Sie uns zum Beispiel, wie Sie heißen, wie alt Sie sind,',
                    'woher Sie kommen, wo Sie wohnen, was Sie beruflich machen',
                    'und was Sie gern in Ihrer Freizeit machen.',
                    'Sie können jetzt beginnen.',
                ]),

                'analysis' => [
                    'expected_topics' => [
                        'name',
                        'age',
                        'origin',
                        'residence',
                        'profession',
                        'hobbies',
                    ],
                ],

                'meta' => [
                    'ai_role' => 'examiner',
                    'part' => 1,
                    'requires_response' => true,
                ],
            ]);
        });
    }



    public function addUserAnswer(
        SpeakingSession $session,
        string $text,
        array $analysis
    ): SpeakingTurn {
        return DB::transaction(function () use ($session, $text, $analysis) {
            $normalizedAnalysis = app(
                SpeakingResponseAnalysisService::class
            )->normalizePartOneAnalysis($analysis);

            $currentCoveredTopics = $session->covered_topics ?? [];

            foreach (
                $normalizedAnalysis['covered_topics'] as $topic
            ) {
                $currentCoveredTopics[$topic] = true;
            }

            $session->update([
                'covered_topics' => $currentCoveredTopics,
            ]);

            $nextTurnNumber =
                ((int) $session->turns()->max('turn_number')) + 1;

            return SpeakingTurn::create([
                'speaking_session_id' => $session->id,
                'exam_part_id' => $session->exam_part_id,
                'speaker' => 'user',
                'text' => $text,
                'turn_number' => $nextTurnNumber,
                'turn_type' => 'answer',
                'analysis' => $normalizedAnalysis,
                'meta' => [
                    'input_type' => 'text',
                    'part' => 1,
                ],
            ]);
        });
    }


    public function createAiFollowUp(
        SpeakingSession $session,
        array $analysis
    ): SpeakingTurn {
        return DB::transaction(function () use ($session, $analysis) {
            $aiResponse = app(SpeakingAiService::class)
                ->generatePartOneFollowUp($session, $analysis);

            /*
         * إذا كان هذا سؤال المتابعة الشخصي،
         * نسجل أنه تم طرحه حتى لا يتكرر.
         */
            if ($aiResponse['type'] === 'personal_follow_up') {
                $sessionMeta = $session->meta ?? [];

                $sessionMeta['personal_follow_up_asked'] = true;

                $session->update([
                    'meta' => $sessionMeta,
                ]);
            }

            $nextTurnNumber =
                ((int) $session->turns()->max('turn_number')) + 1;

            $turnType = match ($aiResponse['type']) {
                'part_complete' => 'transition',
                'personal_follow_up' => 'personal_follow_up',
                default => 'follow_up',
            };

            return SpeakingTurn::create([
                'speaking_session_id' => $session->id,
                'exam_part_id' => $session->exam_part_id,

                'speaker' => 'ai',

                'text' => $aiResponse['text'],

                'turn_number' => $nextTurnNumber,

                'turn_type' => $turnType,

                'analysis' => [
                    'target_topic' => $aiResponse['target_topic'],
                ],

                'meta' => [
                    'ai_role' => 'examiner',
                    'part' => 1,
                    'requires_response' =>
                    $aiResponse['type'] !== 'part_complete',
                ],
            ]);
        });
    }
}

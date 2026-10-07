<?php

namespace App\Services\Speaking;

use App\Models\SpeakingSession;

class SpeakingAiService
{


    public function generatePartOneFollowUp(
        SpeakingSession $session,
        array $analysis
    ): array {
        $session = $session->fresh();

        $coveredTopics = $session->covered_topics ?? [];

        $missingTopics = array_keys(
            array_filter(
                $coveredTopics,
                fn(bool $covered) => ! $covered
            )
        );

        $sessionMeta = $session->meta ?? [];

        /*
     * 1. ما زالت هناك معلومات أساسية ناقصة.
     */
        if (! empty($missingTopics)) {
            $targetTopic = $missingTopics[0];

            $fallbackQuestions = [
                'name' => 'Wie heißen Sie?',
                'age' => 'Wie alt sind Sie?',
                'origin' => 'Woher kommen Sie?',
                'residence' => 'Wo wohnen Sie?',
                'profession' => 'Was machen Sie beruflich?',
                'hobbies' => 'Was machen Sie gern in Ihrer Freizeit?',
            ];

            return [
                'type' => 'follow_up',

                'text' => $analysis['suggested_follow_up']
                    ?? $fallbackQuestions[$targetTopic],

                'target_topic' => $targetTopic,
            ];
        }

        /*
     * 2. جميع المعلومات الأساسية موجودة،
     *    لكننا لم نطرح بعد سؤالًا شخصيًا.
     */
        if (! ($sessionMeta['personal_follow_up_asked'] ?? false)) {
            $suggestedFollowUp =
                $analysis['suggested_follow_up'] ?? null;

            return [
                'type' => 'personal_follow_up',

                'text' => $suggestedFollowUp
                    ?? 'Können Sie uns noch etwas mehr darüber erzählen?',

                'target_topic' => null,
            ];
        }

        /*
     * 3. تم طرح السؤال الشخصي والإجابة عنه.
     *    Teil 1 انتهى.
     */
        return [
            'type' => 'part_complete',

            'text' =>
            'Vielen Dank. Damit ist Teil 1 beendet. Jetzt kommen wir zu Teil 2.',

            'target_topic' => null,
        ];
    }
}

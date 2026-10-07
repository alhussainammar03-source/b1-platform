<?php

namespace Tests\Feature;

use App\Models\ExamFormat;
use App\Models\ExamPart;
use App\Models\SpeakingSession;
use App\Models\User;
use App\Services\Speaking\SpeakingConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ExamSection;
class SpeakingConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_part_one_conversation_can_progress_to_personal_follow_up(): void
    {
        $user = User::factory()->create();
        $examSection = ExamSection::create([
            'key' => 'speaking-test',
            'name' => ['de' => 'Sprechen'],
            'description' => ['de' => 'Sprechen Test'],
            'icon' => 'microphone',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $examFormat = ExamFormat::create([
            'key' => 'dtz-test',
            'name' => ['de' => 'DTZ Test'],
            'level' => 'B1',
            'is_active' => true,
        ]);

        $examPart = ExamPart::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'introduction-test',
            'task_kind' => 'speaking_intro',
            'title' => ['de' => 'Teil 1'],
            'label' => ['de' => 'Vorstellung'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $session = SpeakingSession::create([
            'user_id' => $user->id,
            'exam_format_id' => $examFormat->id,
            'exam_part_id' => $examPart->id,
            'mode' => 'practice',
            'status' => 'in_progress',
            'current_part' => 1,
        ]);

        $service = app(SpeakingConversationService::class);

        /*
         * Turn 1: AI starts Teil 1.
         */
        $greeting = $service->startPartOne($session);

        $this->assertSame('ai', $greeting->speaker);
        $this->assertSame('greeting', $greeting->turn_type);
        $this->assertSame(1, $greeting->turn_number);

        /*
         * Turn 2: User gives some of the required information.
         */
        $firstAnswer = $service->addUserAnswer(
            $session,
            'Ich heiße Ahmad. Ich bin 30 Jahre alt. Ich komme aus Syrien und wohne in Essen.',
            [
                'covered_topics' => [
                    'name',
                    'age',
                    'origin',
                    'residence',
                ],
                'extracted_information' => [
                    'name' => 'Ahmad',
                    'age' => 30,
                    'origin' => 'Syrien',
                    'residence' => 'Essen',
                ],
                'suggested_follow_up' => 'Was machen Sie beruflich?',
            ]
        );

        $this->assertSame(2, $firstAnswer->turn_number);

        /*
         * Turn 3: AI asks about a missing topic.
         */
        $firstFollowUp = $service->createAiFollowUp(
            $session,
            $firstAnswer->analysis
        );

        $this->assertSame('follow_up', $firstFollowUp->turn_type);
        $this->assertSame(
            'Was machen Sie beruflich?',
            $firstFollowUp->text
        );

        /*
         * Turn 4: User answers profession + hobbies.
         */
        $secondAnswer = $service->addUserAnswer(
            $session,
            'Ich arbeite als Softwareentwickler. In meiner Freizeit spiele ich gern Fußball und gehe ins Fitnessstudio.',
            [
                'covered_topics' => [
                    'profession',
                    'hobbies',
                ],
                'extracted_information' => [
                    'profession' => 'Softwareentwickler',
                    'hobbies' => [
                        'Fußball',
                        'Fitnessstudio',
                    ],
                ],
                'suggested_follow_up' =>
                'Wie oft spielen Sie Fußball?',
            ]
        );

        $this->assertSame(4, $secondAnswer->turn_number);

        /*
         * All six required topics should now be covered.
         */
        $session->refresh();

        foreach (
            [
                'name',
                'age',
                'origin',
                'residence',
                'profession',
                'hobbies',
            ] as $topic
        ) {
            $this->assertTrue(
                $session->covered_topics[$topic]
            );
        }

        /*
         * Turn 5: AI asks one personal follow-up question.
         */
        $personalFollowUp = $service->createAiFollowUp(
            $session,
            $secondAnswer->analysis
        );

        $this->assertSame(
            'personal_follow_up',
            $personalFollowUp->turn_type
        );

        $this->assertSame(
            'Wie oft spielen Sie Fußball?',
            $personalFollowUp->text
        );

        $session->refresh();

        $this->assertTrue(
            $session->meta['personal_follow_up_asked']
        );

        /*
         * Turn 6: User answers the personal question.
         */
        $thirdAnswer = $service->addUserAnswer(
            $session,
            'Ich spiele normalerweise zweimal pro Woche Fußball.',
            [
                'covered_topics' => [],
                'extracted_information' => [
                    'football_frequency' => 'zweimal pro Woche',
                ],
                'suggested_follow_up' => null,
            ]
        );

        $this->assertSame(6, $thirdAnswer->turn_number);

        /*
         * Turn 7: AI finishes Teil 1.
         */
        $transition = $service->createAiFollowUp(
            $session,
            $thirdAnswer->analysis
        );

        $this->assertSame('transition', $transition->turn_type);

        $this->assertFalse(
            $transition->meta['requires_response']
        );

        $this->assertSame(7, $transition->turn_number);

        /*
         * Helper must return the real latest user turn.
         */
        $latestUserTurn = $session->latestUserTurn();

        $this->assertNotNull($latestUserTurn);
        $this->assertSame(6, $latestUserTurn->turn_number);
    }
}

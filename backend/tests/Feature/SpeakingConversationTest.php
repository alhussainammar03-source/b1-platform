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
use App\Services\Speaking\Contracts\SpeakingAiProvider;
use App\Services\Speaking\Providers\FakeSpeakingAiProvider;
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




    public function test_authenticated_user_can_start_a_speaking_session(): void
    {
        $user = User::factory()->create();

        $examSection = ExamSection::create([
            'key' => 'speaking',
            'name' => ['de' => 'Sprechen'],
            'description' => ['de' => 'Sprechen'],
            'icon' => 'microphone',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $examFormat = ExamFormat::create([
            'key' => 'dtz',
            'name' => ['de' => 'DTZ'],
            'level' => 'B1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ExamPart::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'introduction',
            'task_kind' => 'speaking_intro',
            'title' => ['de' => 'Teil 1'],
            'label' => ['de' => 'Vorstellung'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson('/api/v1/speaking/sessions');

        $response
            ->assertCreated()
            ->assertJsonPath('session.mode', 'practice')
            ->assertJsonPath('session.status', 'in_progress')
            ->assertJsonPath('session.current_part', 1)
            ->assertJsonPath('turn.speaker', 'ai')
            ->assertJsonPath('turn.turn_number', 1)
            ->assertJsonPath('turn.turn_type', 'greeting')
            ->assertJsonPath('turn.meta.requires_response', true);

        $this->assertDatabaseHas('speaking_sessions', [
            'user_id' => $user->id,
            'exam_format_id' => $examFormat->id,
            'status' => 'in_progress',
            'current_part' => 1,
        ]);

        $this->assertDatabaseHas('speaking_turns', [
            'speaker' => 'ai',
            'turn_number' => 1,
            'turn_type' => 'greeting',
        ]);
    }

    public function test_guest_cannot_start_a_speaking_session(): void
    {
        $response = $this->postJson('/api/v1/speaking/sessions');

        $response->assertUnauthorized();

        $this->assertDatabaseCount('speaking_sessions', 0);
        $this->assertDatabaseCount('speaking_turns', 0);
    }


    public function test_user_cannot_submit_answer_to_another_users_speaking_session(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $examSection = ExamSection::create([
            'key' => 'speaking',
            'name' => ['de' => 'Sprechen'],
            'description' => ['de' => 'Sprechen'],
            'icon' => 'microphone',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $examFormat = ExamFormat::create([
            'key' => 'dtz',
            'name' => ['de' => 'DTZ'],
            'level' => 'B1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $examPart = ExamPart::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'introduction',
            'task_kind' => 'speaking_intro',
            'title' => ['de' => 'Teil 1'],
            'label' => ['de' => 'Vorstellung'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $session = SpeakingSession::create([
            'user_id' => $owner->id,
            'exam_format_id' => $examFormat->id,
            'exam_part_id' => $examPart->id,
            'mode' => 'practice',
            'status' => 'in_progress',
            'current_part' => 1,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->postJson(
                "/api/v1/speaking/sessions/{$session->id}/turns",
                [
                    'text' => 'Ich heiße Ahmad.',
                ]
            );

        $response->assertForbidden();

        $this->assertDatabaseCount('speaking_turns', 0);
    }

    public function test_user_cannot_submit_empty_speaking_answer(): void
    {
        $user = User::factory()->create();

        $examSection = ExamSection::create([
            'key' => 'speaking',
            'name' => ['de' => 'Sprechen'],
            'description' => ['de' => 'Sprechen'],
            'icon' => 'microphone',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $examFormat = ExamFormat::create([
            'key' => 'dtz',
            'name' => ['de' => 'DTZ'],
            'level' => 'B1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $examPart = ExamPart::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'introduction',
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

        $response = $this
            ->actingAs($user)
            ->postJson(
                "/api/v1/speaking/sessions/{$session->id}/turns",
                [
                    'text' => '',
                ]
            );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['text']);

        $this->assertDatabaseCount('speaking_turns', 0);
    }


    public function test_authenticated_user_can_submit_speaking_answer_and_receive_ai_follow_up(): void
    {
        $user = User::factory()->create();

        $examSection = ExamSection::create([
            'key' => 'speaking',
            'name' => ['de' => 'Sprechen'],
            'description' => ['de' => 'Sprechen'],
            'icon' => 'microphone',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $examFormat = ExamFormat::create([
            'key' => 'dtz',
            'name' => ['de' => 'DTZ'],
            'level' => 'B1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $examPart = ExamPart::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'introduction',
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

        $conversationService = app(SpeakingConversationService::class);

        $conversationService->startPartOne($session);

        $provider = app(SpeakingAiProvider::class);

        $this->assertInstanceOf(
            FakeSpeakingAiProvider::class,
            $provider
        );

        $provider->setPartOneAnalysis([
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

            'suggested_follow_up' =>
            'Was machen Sie beruflich?',
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(
                "/api/v1/speaking/sessions/{$session->id}/turns",
                [
                    'text' => implode(' ', [
                        'Ich heiße Ahmad.',
                        'Ich bin 30 Jahre alt.',
                        'Ich komme aus Syrien',
                        'und wohne in Essen.',
                    ]),
                ]
            );

        $response
            ->assertCreated()
            ->assertJsonPath(
                'user_turn.turn_number',
                2
            )
            ->assertJsonPath(
                'user_turn.turn_type',
                'answer'
            )
            ->assertJsonPath(
                'ai_turn.turn_number',
                3
            )
            ->assertJsonPath(
                'ai_turn.turn_type',
                'follow_up'
            )
            ->assertJsonPath(
                'ai_turn.text',
                'Was machen Sie beruflich?'
            );

        $session->refresh();

        $this->assertTrue(
            $session->covered_topics['name']
        );

        $this->assertTrue(
            $session->covered_topics['age']
        );

        $this->assertTrue(
            $session->covered_topics['origin']
        );

        $this->assertTrue(
            $session->covered_topics['residence']
        );

        $this->assertFalse(
            $session->covered_topics['profession']
        );

        $this->assertFalse(
            $session->covered_topics['hobbies']
        );

        $this->assertDatabaseHas('speaking_turns', [
            'speaking_session_id' => $session->id,
            'speaker' => 'user',
            'turn_number' => 2,
            'turn_type' => 'answer',
        ]);

        $this->assertDatabaseHas('speaking_turns', [
            'speaking_session_id' => $session->id,
            'speaker' => 'ai',
            'turn_number' => 3,
            'turn_type' => 'follow_up',
        ]);
    }

}

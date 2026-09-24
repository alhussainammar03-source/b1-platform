<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesAttemptTestData;
use Tests\TestCase;

class AttemptSecurityTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAttemptTestData;

    public function test_answer_option_must_belong_to_submitted_question(): void
    {
        $data = $this->createAttemptTestData();

        // نحاول استخدام Option تابع للسؤال الثاني كإجابة للسؤال الأول
        $response = $this
            ->actingAs($data['user'], 'sanctum')
            ->postJson("/api/v1/attempts/{$data['attempt']->id}/submit", [
                'answers' => [
                    [
                        'question_id' => $data['question1']->id,
                        'answer_option_id' => $data['correctOption2']->id,
                    ],
                ],
            ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('attempt_answers', [
            'attempt_id' => $data['attempt']->id,
            'question_id' => $data['question1']->id,
        ]);

        $this->assertDatabaseHas('attempts', [
            'id' => $data['attempt']->id,
            'status' => 'in_progress',
        ]);
    }




    public function test_user_cannot_submit_another_users_attempt(): void
    {
        $data = $this->createAttemptTestData();

        $otherUser = User::factory()->create();

        $response = $this
            ->actingAs($otherUser, 'sanctum')
            ->postJson("/api/v1/attempts/{$data['attempt']->id}/submit", [
                'answers' => [
                    [
                        'question_id' => $data['question1']->id,
                        'answer_option_id' => $data['correctOption1']->id,
                    ],
                ],
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('attempt_answers', [
            'attempt_id' => $data['attempt']->id,
        ]);

        $this->assertDatabaseHas('attempts', [
            'id' => $data['attempt']->id,
            'user_id' => $data['user']->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_attempt_cannot_be_submitted_twice(): void
    {
        $data = $this->createAttemptTestData();

        $payload = [
            'answers' => [
                [
                    'question_id' => $data['question1']->id,
                    'answer_option_id' => $data['correctOption1']->id,
                ],
                [
                    'question_id' => $data['question2']->id,
                    'answer_option_id' => $data['correctOption2']->id,
                ],
            ],
        ];

        // أول Submit يجب أن ينجح
        $firstResponse = $this
            ->actingAs($data['user'], 'sanctum')
            ->postJson(
                "/api/v1/attempts/{$data['attempt']->id}/submit",
                $payload
            );

        $firstResponse
            ->assertOk()
            ->assertJsonPath('data.status', 'graded')
            ->assertJsonPath('data.percentage', '100.00');

        // نحاول Submit مرة ثانية
        $secondResponse = $this
            ->actingAs($data['user'], 'sanctum')
            ->postJson(
                "/api/v1/attempts/{$data['attempt']->id}/submit",
                $payload
            );

        $secondResponse
            ->assertStatus(422)
            ->assertJsonPath(
                'message',
                'Attempt has already been submitted.'
            );

        // يجب ألا تتكرر الإجابات في قاعدة البيانات
        $this->assertDatabaseCount('attempt_answers', 2);

        $this->assertDatabaseHas('attempts', [
            'id' => $data['attempt']->id,
            'status' => 'graded',
            'score' => 2.00,
            'max_score' => 2.00,
            'percentage' => 100.00,
        ]);
    }

}

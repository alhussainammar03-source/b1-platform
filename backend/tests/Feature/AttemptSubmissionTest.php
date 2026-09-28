<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesAttemptTestData;
use App\Enums\QuestionType;
use App\Models\AnswerOption;
use Tests\TestCase;

class AttemptSubmissionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAttemptTestData;

    public function test_user_can_submit_correct_answers_and_receive_full_score(): void
    {
        $data = $this->createAttemptTestData();

        $response = $this
            ->actingAs($data['user'], 'sanctum')
            ->postJson("/api/v1/attempts/{$data['attempt']->id}/submit", [
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
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'graded')
            ->assertJsonPath('data.score', '2.00')
            ->assertJsonPath('data.max_score', '2.00')
            ->assertJsonPath('data.percentage', '100.00');

        $this->assertDatabaseHas('attempt_answers', [
            'attempt_id' => $data['attempt']->id,
            'question_id' => $data['question1']->id,
            'is_correct' => true,
        ]);

        $this->assertDatabaseHas('attempt_answers', [
            'attempt_id' => $data['attempt']->id,
            'question_id' => $data['question2']->id,
            'is_correct' => true,
        ]);

        $this->assertDatabaseHas('attempts', [
            'id' => $data['attempt']->id,
            'status' => 'graded',
            'score' => 2.00,
            'max_score' => 2.00,
            'percentage' => 100.00,
        ]);
    }


    public function test_user_receives_partial_score_for_one_correct_and_one_wrong_answer(): void
    {
        $data = $this->createAttemptTestData();

        $response = $this
            ->actingAs($data['user'], 'sanctum')
            ->postJson("/api/v1/attempts/{$data['attempt']->id}/submit", [
                'answers' => [
                    [
                        'question_id' => $data['question1']->id,
                        'answer_option_id' => $data['correctOption1']->id,
                    ],
                    [
                        'question_id' => $data['question2']->id,
                        'answer_option_id' => $data['wrongOption2']->id,
                    ],
                ],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'graded')
            ->assertJsonPath('data.score', '1.00')
            ->assertJsonPath('data.max_score', '2.00')
            ->assertJsonPath('data.percentage', '50.00');

        $this->assertDatabaseHas('attempt_answers', [
            'attempt_id' => $data['attempt']->id,
            'question_id' => $data['question1']->id,
            'is_correct' => true,
            'awarded_points' => 1.00,
        ]);

        $this->assertDatabaseHas('attempt_answers', [
            'attempt_id' => $data['attempt']->id,
            'question_id' => $data['question2']->id,
            'is_correct' => false,
            'awarded_points' => 0.00,
        ]);

        $this->assertDatabaseHas('attempts', [
            'id' => $data['attempt']->id,
            'status' => 'graded',
            'score' => 1.00,
            'max_score' => 2.00,
            'percentage' => 50.00,
        ]);
    }




    public function test_true_false_question_is_graded_correctly(): void
    {
        $data = $this->createAttemptTestData();

        $question = $data['question1'];

        $question->update([
            'type' => QuestionType::TRUE_FALSE,
            'prompt' => 'Das Sommerfest beginnt um 18 Uhr.',
        ]);

        $question->answerOptions()->delete();

        $correctOption = AnswerOption::create([
            'question_id' => $question->id,
            'text' => 'Richtig',
            'is_correct' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        AnswerOption::create([
            'question_id' => $question->id,
            'text' => 'Falsch',
            'is_correct' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($data['user'], 'sanctum')
            ->postJson("/api/v1/attempts/{$data['attempt']->id}/submit", [
                'answers' => [
                    [
                        'question_id' => $question->id,
                        'answer_option_id' => $correctOption->id,
                    ],
                    [
                        'question_id' => $data['question2']->id,
                        'answer_option_id' => $data['correctOption2']->id,
                    ],
                ],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'graded')
            ->assertJsonPath('data.score', '2.00')
            ->assertJsonPath('data.max_score', '2.00')
            ->assertJsonPath('data.percentage', '100.00');

        $this->assertDatabaseHas('attempt_answers', [
            'attempt_id' => $data['attempt']->id,
            'question_id' => $question->id,
            'answer_option_id' => $correctOption->id,
            'is_correct' => true,
            'awarded_points' => 1.00,
        ]);
    }
}

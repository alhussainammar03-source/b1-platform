<?php

namespace Tests\Feature;

use App\Jobs\EvaluateWritingJob;
use App\Models\ExamFormat;
use App\Models\ExamSection;
use App\Models\Exercise;
use App\Models\Language;
use App\Models\Question;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\WritingEvaluation;
use App\Services\Writing\WritingEvaluationService;
use Tests\Fakes\FakeWritingEvaluationService;

class WritingEvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_writing_evaluation_with_account_language(): void
    {
        Queue::fake();

        $language = Language::create([
            'code' => 'ar',
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'direction' => 'rtl',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $user = User::factory()->create([
            'language_id' => $language->id,
        ]);

        $examFormat = ExamFormat::create([
            'key' => 'dtz-test',
            'name' => [
                'de' => 'Deutsch-Test für Zuwanderer',
            ],
            'level' => 'A2-B1',
            'is_active' => true,
        ]);

        $examSection = ExamSection::create([
            'key' => 'writing-test',
            'name' => [
                'de' => 'Schreiben',
            ],
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'writing-evaluation-test',
            'type' => 'writing_text',
            'title' => [
                'de' => 'E-Mail schreiben',
            ],
            'instructions' => 'Schreiben Sie eine E-Mail.',
            'difficulty' => 'medium',
            'access_level' => 'premium',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $question = Question::create([
            'exercise_id' => $exercise->id,
            'type' => 'writing_text',
            'prompt' => 'Schreiben Sie eine E-Mail an Frau Berger.',
            'instructions' => 'Bearbeiten Sie alle vier Punkte.',
            'points' => 0,
            'meta' => [
                'required_points' => [
                    'Grund für die Abwesenheit',
                    'Fehltage nennen',
                    'Nach Hausaufgaben fragen',
                    'Um Unterrichtsmaterialien bitten',
                ],
                'ai_evaluation' => true,
            ],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $submission = WritingSubmission::create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'question_id' => $question->id,
            'input_method' => 'text',
            'original_text' => 'Ich schreibe eine E-Mail.',
            'confirmed_text' => 'Ich schreibe eine E-Mail.',
            'status' => 'ready_for_evaluation',
            'confirmed_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/v1/writing/submissions/{$submission->id}/evaluate"
        );

        $response
            ->assertAccepted()
            ->assertJsonPath(
                'data.writing_submission_id',
                $submission->id
            )
            ->assertJsonPath(
                'data.feedback_language',
                'ar'
            )
            ->assertJsonPath(
                'data.status',
                'pending'
            );

        $this->assertDatabaseHas('writing_evaluations', [
            'writing_submission_id' => $submission->id,
            'feedback_language' => 'ar',
            'status' => 'pending',
        ]);

        Queue::assertPushed(
            EvaluateWritingJob::class,
            fn(EvaluateWritingJob $job) =>
            $job->evaluationId === $response->json('data.id')
        );
    }


    public function test_user_cannot_evaluate_another_users_submission(): void
    {
        Queue::fake();

        $language = Language::create([
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'direction' => 'ltr',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $owner = User::factory()->create([
            'language_id' => $language->id,
        ]);

        $otherUser = User::factory()->create([
            'language_id' => $language->id,
        ]);

        $examFormat = ExamFormat::create([
            'key' => 'dtz-security-test',
            'name' => ['de' => 'DTZ'],
            'level' => 'A2-B1',
            'is_active' => true,
        ]);

        $examSection = ExamSection::create([
            'key' => 'writing-security-test',
            'name' => ['de' => 'Schreiben'],
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'writing-security-exercise',
            'type' => 'writing_text',
            'title' => ['de' => 'E-Mail schreiben'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $question = Question::create([
            'exercise_id' => $exercise->id,
            'type' => 'writing_text',
            'prompt' => 'Schreiben Sie eine E-Mail.',
            'points' => 0,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $submission = WritingSubmission::create([
            'user_id' => $owner->id,
            'exercise_id' => $exercise->id,
            'question_id' => $question->id,
            'input_method' => 'text',
            'original_text' => 'Test',
            'confirmed_text' => 'Test',
            'status' => 'ready_for_evaluation',
            'confirmed_at' => now(),
        ]);

        Sanctum::actingAs($otherUser);

        $this->postJson(
            "/api/v1/writing/submissions/{$submission->id}/evaluate"
        )->assertForbidden();

        $this->assertDatabaseCount('writing_evaluations', 0);

        Queue::assertNothingPushed();
    }

    public function test_submission_must_be_ready_before_evaluation(): void
    {
        Queue::fake();

        $language = Language::create([
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'direction' => 'ltr',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $user = User::factory()->create([
            'language_id' => $language->id,
        ]);

        $examFormat = ExamFormat::create([
            'key' => 'dtz-status-test',
            'name' => ['de' => 'DTZ'],
            'level' => 'A2-B1',
            'is_active' => true,
        ]);

        $examSection = ExamSection::create([
            'key' => 'writing-status-test',
            'name' => ['de' => 'Schreiben'],
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'writing-status-exercise',
            'type' => 'writing_text',
            'title' => ['de' => 'E-Mail schreiben'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $question = Question::create([
            'exercise_id' => $exercise->id,
            'type' => 'writing_text',
            'prompt' => 'Schreiben Sie eine E-Mail.',
            'points' => 0,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $submission = WritingSubmission::create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'question_id' => $question->id,
            'input_method' => 'handwritten_image',
            'status' => 'awaiting_confirmation',
        ]);

        Sanctum::actingAs($user);

        $this->postJson(
            "/api/v1/writing/submissions/{$submission->id}/evaluate"
        )->assertUnprocessable();

        $this->assertDatabaseCount('writing_evaluations', 0);

        Queue::assertNothingPushed();
    }

    public function test_user_can_override_feedback_language(): void
    {
        Queue::fake();

        $language = Language::create([
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'direction' => 'ltr',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $user = User::factory()->create([
            'language_id' => $language->id,
        ]);

        $examFormat = ExamFormat::create([
            'key' => 'dtz-language-test',
            'name' => ['de' => 'DTZ'],
            'level' => 'A2-B1',
            'is_active' => true,
        ]);

        $examSection = ExamSection::create([
            'key' => 'writing-language-test',
            'name' => ['de' => 'Schreiben'],
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'writing-language-exercise',
            'type' => 'writing_text',
            'title' => ['de' => 'E-Mail schreiben'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $question = Question::create([
            'exercise_id' => $exercise->id,
            'type' => 'writing_text',
            'prompt' => 'Schreiben Sie eine E-Mail.',
            'points' => 0,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $submission = WritingSubmission::create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'question_id' => $question->id,
            'input_method' => 'text',
            'original_text' => 'Ich schreibe eine E-Mail.',
            'confirmed_text' => 'Ich schreibe eine E-Mail.',
            'status' => 'ready_for_evaluation',
            'confirmed_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/v1/writing/submissions/{$submission->id}/evaluate",
            [
                'feedback_language' => 'uk',
            ]
        );

        $response
            ->assertAccepted()
            ->assertJsonPath('data.feedback_language', 'uk');

        $this->assertDatabaseHas('writing_evaluations', [
            'writing_submission_id' => $submission->id,
            'feedback_language' => 'uk',
        ]);

        Queue::assertPushed(EvaluateWritingJob::class);
    }


    private function createWritingSubmission(
        User $user,
        string $status = 'ready_for_evaluation'
    ): WritingSubmission {
        $examFormat = ExamFormat::create([
            'key' => 'dtz-' . uniqid(),
            'name' => ['de' => 'DTZ'],
            'level' => 'A2-B1',
            'is_active' => true,
        ]);

        $examSection = ExamSection::create([
            'key' => 'writing-' . uniqid(),
            'name' => ['de' => 'Schreiben'],
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'writing-exercise-' . uniqid(),
            'type' => 'writing_text',
            'title' => ['de' => 'E-Mail schreiben'],
            'instructions' => 'Schreiben Sie eine E-Mail.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $question = Question::create([
            'exercise_id' => $exercise->id,
            'type' => 'writing_text',
            'prompt' => 'Schreiben Sie eine E-Mail an Frau Berger.',
            'instructions' => 'Bearbeiten Sie alle vier Punkte.',
            'points' => 0,
            'meta' => [
                'required_points' => [
                    'Grund für die Abwesenheit',
                    'Fehltage nennen',
                    'Nach Hausaufgaben fragen',
                    'Um Unterrichtsmaterialien bitten',
                ],
                'ai_evaluation' => true,
            ],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return WritingSubmission::create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'question_id' => $question->id,
            'input_method' => 'text',
            'original_text' => 'Ich schreibe eine E-Mail.',
            'confirmed_text' => 'Ich schreibe eine E-Mail.',
            'status' => $status,
            'confirmed_at' => $status === 'ready_for_evaluation'
                ? now()
                : null,
        ]);
    }


    public function test_unsupported_feedback_language_is_rejected(): void
    {
        Queue::fake();

        $language = Language::create([
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'direction' => 'ltr',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $user = User::factory()->create([
            'language_id' => $language->id,
        ]);

        $submission = $this->createWritingSubmission($user);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/v1/writing/submissions/{$submission->id}/evaluate",
            [
                'feedback_language' => 'fr',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('feedback_language');

        $this->assertDatabaseCount('writing_evaluations', 0);

        Queue::assertNothingPushed();
    }



    public function test_evaluation_job_saves_ai_feedback(): void
    {
        $language = Language::create([
            'code' => 'ar',
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'direction' => 'rtl',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $user = User::factory()->create([
            'language_id' => $language->id,
        ]);

        $submission = $this->createWritingSubmission($user);

        $evaluation = WritingEvaluation::create([
            'writing_submission_id' => $submission->id,
            'feedback_language' => 'ar',
            'status' => 'pending',
        ]);

        $this->app->bind(
            WritingEvaluationService::class,
            FakeWritingEvaluationService::class
        );

        $job = new EvaluateWritingJob($evaluation->id);

        $job->handle(
            $this->app->make(WritingEvaluationService::class)
        );

        $evaluation->refresh();
        $submission->refresh();

        $this->assertSame('evaluated', $evaluation->status);
        $this->assertSame('evaluated', $submission->status);

        $this->assertSame(
            'openai',
            $evaluation->provider
        );

        $this->assertNotNull($evaluation->evaluated_at);

        $this->assertSame(
            'Sehr geehrte Frau Berger, ich kann leider nicht kommen.',
            $evaluation->corrected_text
        );

        $this->assertSame(
            'Der Text ist verständlich. Achte besonders auf die Satzstellung.',
            $evaluation->feedback_de
        );

        $this->assertSame(
            'النص مفهوم. انتبه بشكل خاص إلى ترتيب الكلمات.',
            $evaluation->feedback_translated
        );

        $this->assertSame(
            'Die Aufgabe wurde erfüllt.',
            $evaluation->criteria['task_completion']['feedback_de']
        );

        $this->assertSame(
            'تم تنفيذ المهمة.',
            $evaluation->criteria['task_completion']['feedback_translated']
        );

        $this->assertSame(
            'weil ich krank bin',
            $evaluation->errors[0]['correction']
        );

        $this->assertSame(
            'word_order',
            $evaluation->errors[0]['category']
        );

        $this->assertSame(
            'انتبه إلى موقع الفعل في الجمل الفرعية.',
            $evaluation->focus_points[0]['text_translated']
        );
    }



    public function test_user_can_view_own_writing_evaluation(): void
    {
        $user = User::factory()->create();

        $submission = $this->createWritingSubmission($user);

        $evaluation = WritingEvaluation::create([
            'writing_submission_id' => $submission->id,
            'feedback_language' => 'ar',
            'status' => 'evaluated',
            'corrected_text' => 'Korrigierter Text',
            'improved_example' => 'Verbessertes Beispiel',
            'feedback_de' => 'Deutsches Feedback',
            'feedback_translated' => 'ملاحظات بالعربية',
            'criteria' => [
                'grammar' => [
                    'feedback_de' => 'Die Grammatik ist gut.',
                    'feedback_translated' => 'القواعد جيدة.',
                ],
            ],
            'errors' => [],
            'missing_required_points' => [],
            'focus_points' => [],
            'evaluated_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/v1/writing/evaluations/{$evaluation->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $evaluation->id)
            ->assertJsonPath('data.writing_submission_id', $submission->id)
            ->assertJsonPath('data.feedback_language', 'ar')
            ->assertJsonPath('data.status', 'evaluated')
            ->assertJsonPath('data.corrected_text', 'Korrigierter Text')
            ->assertJsonPath('data.feedback_de', 'Deutsches Feedback')
            ->assertJsonPath('data.feedback_translated', 'ملاحظات بالعربية')
            ->assertJsonPath(
                'data.criteria.grammar.feedback_translated',
                'القواعد جيدة.'
            );
    }

    public function test_user_cannot_view_another_users_writing_evaluation(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $submission = $this->createWritingSubmission($owner);

        $evaluation = WritingEvaluation::create([
            'writing_submission_id' => $submission->id,
            'feedback_language' => 'de',
            'status' => 'evaluated',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->getJson("/api/v1/writing/evaluations/{$evaluation->id}");

        $response->assertForbidden();
    }
}

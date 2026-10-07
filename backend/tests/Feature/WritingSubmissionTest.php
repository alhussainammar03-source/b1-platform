<?php

namespace Tests\Feature;

use App\Jobs\ExtractHandwritingJob;
use App\Models\ExamFormat;
use App\Models\ExamSection;
use App\Models\Exercise;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\WritingSubmission;

class WritingSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_text_submission_does_not_dispatch_handwriting_extraction_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $examFormat = ExamFormat::create([
            'key' => 'dtz-writing-submission-test',
            'name' => ['de' => 'DTZ'],
            'level' => 'A2-B1',
            'is_active' => true,
        ]);

        $examSection = ExamSection::create([
            'key' => 'writing-submission-test',
            'name' => ['de' => 'Schreiben'],
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'writing-text-submission-test',
            'type' => 'writing_text',
            'title' => ['de' => 'E-Mail schreiben'],
            'instructions' => 'Schreiben Sie eine E-Mail.',
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

        $response = $this
            ->actingAs($user)
            ->postJson('/api/v1/writing/submissions', [
                'question_id' => $question->id,
                'input_method' => 'text',
                'text' => 'Sehr geehrte Frau Berger, ich bin leider krank.',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.input_method', 'text')
            ->assertJsonPath('data.status', 'ready_for_evaluation')
            ->assertJsonPath(
                'data.confirmed_text',
                'Sehr geehrte Frau Berger, ich bin leider krank.'
            );

        $this->assertDatabaseHas('writing_submissions', [
            'user_id' => $user->id,
            'question_id' => $question->id,
            'input_method' => 'text',
            'status' => 'ready_for_evaluation',
            'confirmed_text' => 'Sehr geehrte Frau Berger, ich bin leider krank.',
        ]);

        Queue::assertNotPushed(ExtractHandwritingJob::class);
    }


    public function test_handwritten_image_submission_dispatches_extraction_job(): void
    {
        Storage::fake('local');
        Queue::fake();

        $user = User::factory()->create();

        $examFormat = ExamFormat::create([
            'key' => 'dtz-handwriting-submission-test',
            'name' => ['de' => 'DTZ'],
            'level' => 'A2-B1',
            'is_active' => true,
        ]);

        $examSection = ExamSection::create([
            'key' => 'writing-handwriting-test',
            'name' => ['de' => 'Schreiben'],
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'writing-handwriting-exercise-test',
            'type' => 'writing_text',
            'title' => ['de' => 'E-Mail schreiben'],
            'instructions' => 'Schreiben Sie eine E-Mail.',
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

        $image = UploadedFile::fake()->image(
            'handwriting.jpg',
            1200,
            1600
        );

        $response = $this
            ->actingAs($user)
            ->post('/api/v1/writing/submissions', [
                'question_id' => $question->id,
                'input_method' => 'handwritten_image',
                'image' => $image,
            ], [
                'Accept' => 'application/json',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.input_method', 'handwritten_image')
            ->assertJsonPath('data.status', 'extracting');

        $submissionId = $response->json('data.id');

        $submission = \App\Models\WritingSubmission::findOrFail(
            $submissionId
        );

        $this->assertSame('local', $submission->image_disk);
        $this->assertNotNull($submission->image_path);

        Storage::disk('local')->assertExists(
            $submission->image_path
        );

        Queue::assertPushed(
            ExtractHandwritingJob::class,
            fn(ExtractHandwritingJob $job) =>
            $job->submissionId === $submission->id
        );

        Queue::assertPushed(ExtractHandwritingJob::class, 1);
    }

    public function test_user_can_confirm_extracted_handwriting_text(): void
    {
        $user = User::factory()->create();

        $examFormat = ExamFormat::create([
            'key' => 'dtz-handwriting-confirm-test',
            'name' => ['de' => 'DTZ'],
            'level' => 'A2-B1',
            'is_active' => true,
        ]);

        $examSection = ExamSection::create([
            'key' => 'writing-confirm-test',
            'name' => ['de' => 'Schreiben'],
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'exam_format_id' => $examFormat->id,
            'exam_section_id' => $examSection->id,
            'key' => 'writing-confirm-exercise-test',
            'type' => 'writing_text',
            'title' => ['de' => 'E-Mail schreiben'],
            'instructions' => 'Schreiben Sie eine E-Mail.',
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
            'image_disk' => 'local',
            'image_path' => 'writing-submissions/test/handwriting.jpg',
            'extracted_text' => 'Sehr geehrte Herr Berger',
            'status' => 'awaiting_confirmation',
        ]);

        $confirmedText =
            'Sehr geehrte Frau Berger, leider bin ich krank.';

        $response = $this
            ->actingAs($user)
            ->patchJson(
                "/api/v1/writing/submissions/{$submission->id}/confirm",
                [
                    'confirmed_text' => $confirmedText,
                ]
            );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.status',
                'ready_for_evaluation'
            )
            ->assertJsonPath(
                'data.confirmed_text',
                $confirmedText
            );

        $this->assertDatabaseHas('writing_submissions', [
            'id' => $submission->id,
            'user_id' => $user->id,
            'status' => 'ready_for_evaluation',
            'confirmed_text' => $confirmedText,
        ]);

        $submission->refresh();

        $this->assertNotNull(
            $submission->confirmed_at
        );
    }


    public function test_user_cannot_confirm_another_users_handwriting_submission(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $examFormat = ExamFormat::create([
            'key' => 'dtz-handwriting-security-test',
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
            'key' => 'writing-security-exercise-test',
            'type' => 'writing_text',
            'title' => ['de' => 'E-Mail schreiben'],
            'instructions' => 'Schreiben Sie eine E-Mail.',
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
            'input_method' => 'handwritten_image',
            'image_disk' => 'local',
            'image_path' => 'writing-submissions/test/security.jpg',
            'extracted_text' => 'Test',
            'status' => 'awaiting_confirmation',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->patchJson(
                "/api/v1/writing/submissions/{$submission->id}/confirm",
                [
                    'confirmed_text' => 'Manipulierter Text',
                ]
            );

        $response->assertForbidden();

        $this->assertDatabaseHas('writing_submissions', [
            'id' => $submission->id,
            'user_id' => $owner->id,
            'status' => 'awaiting_confirmation',
            'confirmed_text' => null,
        ]);
    }
}

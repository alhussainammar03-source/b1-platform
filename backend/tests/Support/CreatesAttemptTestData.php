<?php

namespace Tests\Support;

use App\Models\AnswerOption;
use App\Models\Attempt;
use App\Models\ExamFormat;
use App\Models\ExamSection;
use App\Models\Exercise;
use App\Models\Question;
use App\Models\User;

trait CreatesAttemptTestData
{
    protected function createAttemptTestData(): array
    {
        $user = User::factory()->create();

        $format = ExamFormat::create([
            'key' => 'test-format',
            'name' => ['de' => 'Testformat'],
            'level' => 'B1',
            'is_active' => true,
        ]);

        $section = ExamSection::create([
            'key' => 'reading',
            'name' => ['de' => 'Lesen'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'exam_format_id' => $format->id,
            'exam_section_id' => $section->id,
            'key' => 'test-reading-exercise',
            'type' => 'multiple_choice',
            'title' => ['de' => 'Test Lesen'],
            'difficulty' => 'easy',
            'access_level' => 'free',
            'status' => 'published',
        ]);

        $question1 = Question::create([
            'exercise_id' => $exercise->id,
            'type' => 'multiple_choice',
            'prompt' => 'Frage 1',
            'points' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $question2 = Question::create([
            'exercise_id' => $exercise->id,
            'type' => 'multiple_choice',
            'prompt' => 'Frage 2',
            'points' => 1,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $correctOption1 = AnswerOption::create([
            'question_id' => $question1->id,
            'text' => 'Richtige Antwort 1',
            'is_correct' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $wrongOption1 = AnswerOption::create([
            'question_id' => $question1->id,
            'text' => 'Falsche Antwort 1',
            'is_correct' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $correctOption2 = AnswerOption::create([
            'question_id' => $question2->id,
            'text' => 'Richtige Antwort 2',
            'is_correct' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $wrongOption2 = AnswerOption::create([
            'question_id' => $question2->id,
            'text' => 'Falsche Antwort 2',
            'is_correct' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $attempt = Attempt::create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return compact(
            'user',
            'format',
            'section',
            'exercise',
            'question1',
            'question2',
            'correctOption1',
            'wrongOption1',
            'correctOption2',
            'wrongOption2',
            'attempt',
        );
    }
}

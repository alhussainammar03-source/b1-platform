<?php

namespace Tests\Feature;

use App\Models\ExamFormat;
use App\Models\ExamPart;
use App\Models\ExamSection;
use App\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseLocaleTest extends TestCase
{
    use RefreshDatabase;

    private function createReadingExercise(): Exercise
    {
        $format = ExamFormat::create([
            'key' => 'dtz-test',
            'name' => [
                'de' => 'Deutsch-Test für Zuwanderer',
                'ar' => 'اختبار اللغة الألمانية للمهاجرين',
            ],
            'level' => 'A2-B1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $section = ExamSection::create([
            'key' => 'reading',
            'name' => [
                'de' => 'Lesen',
                'ar' => 'القراءة',
            ],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $part = ExamPart::create([
            'exam_format_id' => $format->id,
            'exam_section_id' => $section->id,
            'key' => 'reading_part_1',
            'task_kind' => 'multiple_choice',
            'title' => [
                'de' => 'Lesen - Teil 1',
                'ar' => 'القراءة - الجزء 1',
            ],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return Exercise::create([
            'exam_format_id' => $format->id,
            'exam_section_id' => $section->id,
            'exam_part_id' => $part->id,
            'key' => 'locale-test-reading',
            'type' => 'multiple_choice',

            'title' => [
                'de' => 'Lesen Teil 1',
                'ar' => 'القراءة الجزء 1',
                'en' => 'Reading Part 1',
            ],

            'description' => [
                'de' => 'Deutsche Beschreibung',
                'ar' => 'وصف عربي',
                'en' => 'English description',
            ],

            'instructions' => 'Lesen Sie den Text.',
            'difficulty' => 'easy',
            'access_level' => 'free',
            'status' => 'published',
            'sort_order' => 1,
            'published_at' => now(),
        ]);
    }

    public function test_exercise_list_uses_requested_locale(): void
    {
        $this->createReadingExercise();

        $response = $this
            ->withHeader('X-Locale', 'ar')
            ->getJson('/api/v1/exercises?section=reading');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.0.title',
                'القراءة الجزء 1'
            )
            ->assertJsonPath(
                'data.0.description',
                'وصف عربي'
            )
            ->assertJsonPath(
                'data.0.part.title',
                'القراءة - الجزء 1'
            );
    }

    public function test_unsupported_locale_falls_back_to_german(): void
    {
        $this->createReadingExercise();

        $response = $this
            ->withHeader('X-Locale', 'fr')
            ->getJson('/api/v1/exercises?section=reading');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.0.title',
                'Lesen Teil 1'
            )
            ->assertJsonPath(
                'data.0.description',
                'Deutsche Beschreibung'
            )
            ->assertJsonPath(
                'data.0.part.title',
                'Lesen - Teil 1'
            );
    }
}

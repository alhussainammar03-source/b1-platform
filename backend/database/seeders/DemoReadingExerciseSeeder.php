<?php

namespace Database\Seeders;

use App\Models\AnswerOption;
use App\Models\ExamFormat;
use App\Models\ExamSection;
use App\Models\Exercise;
use App\Models\ExerciseStimulus;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoReadingExerciseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $format = ExamFormat::where('key', 'dtz')->firstOrFail();

            $section = ExamSection::where('key', 'reading')->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                [
                    'key' => 'dtz-reading-demo-1',
                ],
                [
                    'exam_format_id' => $format->id,
                    'exam_section_id' => $section->id,
                    'exam_part_id' => null,

                    'type' => 'multiple_choice',

                    'title' => [
                        'de' => 'Lesen – Übung 1',
                        'ar' => 'القراءة – التدريب 1',
                        'en' => 'Reading – Exercise 1',
                        'tr' => 'Okuma – Alıştırma 1',
                        'uk' => 'Читання – Вправа 1',
                    ],

                    'description' => [
                        'de' => 'Lesen Sie den Text und beantworten Sie die Fragen.',
                        'ar' => 'اقرأ النص ثم أجب عن الأسئلة.',
                        'en' => 'Read the text and answer the questions.',
                        'tr' => 'Metni okuyun ve soruları cevaplayın.',
                        'uk' => 'Прочитайте текст і дайте відповіді на запитання.',
                    ],

                    'instructions' => 'Lesen Sie den Text und wählen Sie die richtige Antwort.',

                    'difficulty' => 'easy',
                    'access_level' => 'free',
                    'status' => 'published',
                    'sort_order' => 1,
                    'published_at' => now(),
                ]
            );

            // Damit der Seeder mehrfach ausgeführt werden kann.
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',
                'content' => <<<'TEXT'
Liebe Nachbarn,

am Samstag feiern wir ab 18 Uhr ein kleines Sommerfest im Garten hinter unserem Haus.

Für Getränke ist gesorgt. Wer möchte, kann einen Salat oder einen Kuchen mitbringen.

Bei schlechtem Wetter findet das Fest im Gemeinschaftsraum statt.

Viele Grüße
Familie Becker
TEXT,
                'label' => 'Nachricht',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            $question1 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'multiple_choice',
                'prompt' => 'Wann beginnt das Sommerfest?',
                'points' => 1,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question1->id,
                'text' => 'Am Samstag um 18 Uhr.',
                'is_correct' => true,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question1->id,
                'text' => 'Am Sonntag um 18 Uhr.',
                'is_correct' => false,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question1->id,
                'text' => 'Am Samstag um 20 Uhr.',
                'is_correct' => false,
                'sort_order' => 3,
                'is_active' => true,
            ]);

            $question2 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'multiple_choice',
                'prompt' => 'Was passiert bei schlechtem Wetter?',
                'points' => 1,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question2->id,
                'text' => 'Das Fest fällt aus.',
                'is_correct' => false,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question2->id,
                'text' => 'Das Fest findet im Gemeinschaftsraum statt.',
                'is_correct' => true,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question2->id,
                'text' => 'Das Fest wird auf Sonntag verschoben.',
                'is_correct' => false,
                'sort_order' => 3,
                'is_active' => true,
            ]);
        });
    }
}

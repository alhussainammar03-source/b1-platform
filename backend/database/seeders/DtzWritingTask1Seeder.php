<?php

namespace Database\Seeders;

use App\Models\ExamFormat;
use App\Models\ExamPart;
use App\Models\ExamSection;
use App\Models\Exercise;
use App\Models\ExerciseStimulus;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DtzWritingTask1Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $format = ExamFormat::where('key', 'dtz')->firstOrFail();

            $section = ExamSection::where('key', 'writing')->firstOrFail();

            $part = ExamPart::where('exam_format_id', $format->id)
                ->where('exam_section_id', $section->id)
                ->where('key', 'writing_task')
                ->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                [
                    'key' => 'dtz-writing-task-001',
                ],
                [
                    'exam_format_id' => $format->id,
                    'exam_section_id' => $section->id,
                    'exam_part_id' => $part->id,

                    'type' => 'writing_text',

                    'title' => [
                        'de' => 'Schreiben – Übung 1',
                        'ar' => 'الكتابة – التدريب 1',
                        'en' => 'Writing – Exercise 1',
                        'tr' => 'Yazma – Alıştırma 1',
                        'uk' => 'Письмо – Вправа 1',
                    ],

                    'description' => [
                        'de' => 'Schreiben Sie eine E-Mail und bearbeiten Sie alle vier Punkte.',
                        'ar' => 'اكتب رسالة بريد إلكتروني وتناول النقاط الأربع جميعها.',
                        'en' => 'Write an email and address all four points.',
                        'tr' => 'Bir e-posta yazın ve dört noktanın hepsini ele alın.',
                        'uk' => 'Напишіть електронного листа та розкрийте всі чотири пункти.',
                    ],

                    'instructions' => 'Schreiben Sie eine E-Mail. Schreiben Sie etwas zu allen vier Punkten.',

                    'difficulty' => 'easy',
                    'access_level' => 'premium',
                    'status' => 'published',
                    'sort_order' => 1,
                    'published_at' => now(),
                ]
            );

            // Seeder kann mehrfach ausgeführt werden.
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',

                'content' => <<<'TEXT'
Sie besuchen einen Deutschkurs. Nächste Woche können Sie an zwei Kurstagen nicht teilnehmen.

Schreiben Sie eine E-Mail an Ihre Kursleiterin Frau Berger.

Schreiben Sie etwas zu folgenden Punkten:

• Warum können Sie nicht zum Kurs kommen?
• An welchen Tagen fehlen Sie?
• Fragen Sie nach den Hausaufgaben.
• Bitten Sie um die Unterrichtsmaterialien.
TEXT,

                'label' => 'Schreibaufgabe',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'writing_text',

                'prompt' => 'Schreiben Sie Ihre E-Mail an Frau Berger.',

                'instructions' => 'Bearbeiten Sie alle vier Punkte. Achten Sie auf Anrede, Aufbau, Grammatik und Grußformel.',

                // Schreiben wird später durch AI bzw. manuell bewertet.
                'points' => 0,

                'meta' => [
                    'submission_modes' => [
                        'text',
                        'handwritten_image',
                    ],

                    'required_points' => [
                        'Grund für die Abwesenheit',
                        'Fehltage nennen',
                        'Nach Hausaufgaben fragen',
                        'Um Unterrichtsmaterialien bitten',
                    ],

                    'ai_evaluation' => true,
                    'manual_grading' => true,
                ],

                'sort_order' => 1,
                'is_active' => true,
            ]);
        });
    }
}

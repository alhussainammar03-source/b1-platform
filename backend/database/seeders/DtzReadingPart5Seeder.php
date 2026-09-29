<?php

namespace Database\Seeders;

use App\Models\AnswerOption;
use App\Models\ExamFormat;
use App\Models\ExamPart;
use App\Models\ExamSection;
use App\Models\Exercise;
use App\Models\ExerciseStimulus;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DtzReadingPart5Seeder extends Seeder
{
    /**
     * Seed an original DTZ-style reading exercise for Lesen Teil 5.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $examFormat = ExamFormat::where('key', 'dtz')->firstOrFail();
            $examSection = ExamSection::where('key', 'reading')->firstOrFail();

            $examPart = ExamPart::where('exam_format_id', $examFormat->id)
                ->where('exam_section_id', $examSection->id)
                ->where('key', 'reading_part_5')
                ->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                ['key' => 'dtz-reading-part-5-001'],
                [
                    'exam_format_id' => $examFormat->id,
                    'exam_section_id' => $examSection->id,
                    'exam_part_id' => $examPart->id,
                    'type' => 'gap_fill',

                    'title' => [
                        'de' => 'Lesen Teil 5 – Aufgabe 1',
                        'ar' => 'القراءة الجزء 5 – التمرين 1',
                        'en' => 'Reading Part 5 – Exercise 1',
                        'tr' => 'Okuma Bölüm 5 – Alıştırma 1',
                        'uk' => 'Читання, частина 5 – Вправа 1',
                    ],

                    'description' => [
                        'de' => 'Lesen Sie die E-Mail und wählen Sie für jede Lücke das passende Wort.',
                        'ar' => 'اقرأ البريد الإلكتروني واختر الكلمة المناسبة لكل فراغ.',
                        'en' => 'Read the email and choose the correct word for each gap.',
                        'tr' => 'E-postayı okuyun ve her boşluk için uygun kelimeyi seçin.',
                        'uk' => 'Прочитайте електронний лист і виберіть правильне слово для кожного пропуску.',
                    ],

                    'instructions' =>
                    'Lesen Sie den Text. Wählen Sie für jede Lücke die richtige Lösung.',

                    'difficulty' => 'medium',
                    'access_level' => 'free',
                    'status' => 'published',
                    'sort_order' => 1,
                    'published_at' => now(),
                ]
            );

            // Repeatable development seed.
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',
                'label' => 'E-Mail',
                'content' => <<<'TEXT'
Betreff: Termin am Donnerstag

Liebe Frau Schneider,

vielen Dank für Ihre Nachricht. Leider kann ich am Donnerstag nicht um 10 Uhr zu Ihnen kommen, ___(1)___ ich an diesem Vormittag einen wichtigen Arzttermin habe.

Wäre es möglich, dass wir uns am Nachmittag treffen? Ich könnte ___(2)___ 15 Uhr bei Ihnen sein.

Bitte geben Sie mir kurz Bescheid, ___(3)___ der neue Termin für Sie passt.

Vielen Dank für Ihr Verständnis.

Mit freundlichen Grüßen
Karim Hassan
TEXT,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            $this->createGapQuestion(
                $exercise,
                'Lücke 1',
                [
                    ['text' => 'weil', 'correct' => true],
                    ['text' => 'aber', 'correct' => false],
                    ['text' => 'oder', 'correct' => false],
                ],
                1
            );

            $this->createGapQuestion(
                $exercise,
                'Lücke 2',
                [
                    ['text' => 'gegen', 'correct' => true],
                    ['text' => 'seit', 'correct' => false],
                    ['text' => 'aus', 'correct' => false],
                ],
                2
            );

            $this->createGapQuestion(
                $exercise,
                'Lücke 3',
                [
                    ['text' => 'ob', 'correct' => true],
                    ['text' => 'als', 'correct' => false],
                    ['text' => 'denn', 'correct' => false],
                ],
                3
            );
        });
    }

    private function createGapQuestion(
        Exercise $exercise,
        string $prompt,
        array $options,
        int $sortOrder
    ): void {
        $question = Question::create([
            'exercise_id' => $exercise->id,
            'type' => 'gap_fill',
            'prompt' => $prompt,
            'points' => 1,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);

        foreach ($options as $index => $option) {
            AnswerOption::create([
                'question_id' => $question->id,
                'text' => $option['text'],
                'is_correct' => $option['correct'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}

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

class DtzReadingPart2Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $format = ExamFormat::where('key', 'dtz')->firstOrFail();

            $section = ExamSection::where('key', 'reading')->firstOrFail();

            $part = ExamPart::where('exam_format_id', $format->id)
                ->where('exam_section_id', $section->id)
                ->where('key', 'reading_part_2')
                ->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                [
                    'key' => 'dtz-reading-part-2-001',
                ],
                [
                    'exam_format_id' => $format->id,
                    'exam_section_id' => $section->id,
                    'exam_part_id' => $part->id,

                    'type' => 'matching',

                    'title' => [
                        'de' => 'Lesen Teil 2 – Übung 1',
                        'ar' => 'القراءة الجزء 2 – التدريب 1',
                        'en' => 'Reading Part 2 – Exercise 1',
                        'tr' => 'Okuma Bölüm 2 – Alıştırma 1',
                        'uk' => 'Читання, частина 2 – Вправа 1',
                    ],

                    'description' => [
                        'de' => 'Lesen Sie die Situationen und ordnen Sie die passende Anzeige zu.',
                        'ar' => 'اقرأ المواقف واختر الإعلان المناسب لكل موقف.',
                        'en' => 'Read the situations and match each one with the correct advertisement.',
                        'tr' => 'Durumları okuyun ve uygun ilanı eşleştirin.',
                        'uk' => 'Прочитайте ситуації та доберіть відповідне оголошення.',
                    ],

                    'instructions' =>
                    'Lesen Sie die Situationen und die Anzeigen. Wählen Sie für jede Situation die passende Anzeige.',

                    'difficulty' => 'easy',
                    'access_level' => 'free',
                    'status' => 'published',
                    'sort_order' => 1,
                    'published_at' => now(),
                ]
            );

            /*
             * Der Seeder kann mehrfach ausgeführt werden,
             * ohne Fragen oder Texte zu duplizieren.
             */
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            /*
             * Anzeigen A–D
             */
            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',

                'content' => <<<'TEXT'
A – Deutsch am Abend

Sie arbeiten tagsüber und möchten Ihr Deutsch verbessern?

Deutschkurse für Erwachsene
Montag und Mittwoch
18:30–20:30 Uhr

Sprachzentrum West
TEXT,

                'label' => 'Anzeige A',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',

                'content' => <<<'TEXT'
B – Deutsch am Wochenende

Keine Zeit von Montag bis Freitag?

Unser Deutschkurs findet jeden Samstag
von 10:00 bis 14:00 Uhr statt.

Niveau A2–B1
TEXT,

                'label' => 'Anzeige B',
                'sort_order' => 2,
                'is_active' => true,
            ]);

            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',

                'content' => <<<'TEXT'
C – Deutsch für den Beruf

Sie möchten besser mit Kollegen und Kunden sprechen?

Deutsch für Arbeitsplatz und Beruf
Dienstag und Donnerstag
17:00–19:00 Uhr

Ab Niveau B1
TEXT,

                'label' => 'Anzeige C',
                'sort_order' => 3,
                'is_active' => true,
            ]);

            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',

                'content' => <<<'TEXT'
D – Deutsch am Vormittag

Deutsch lernen in kleinen Gruppen

Montag bis Donnerstag
09:00–11:30 Uhr

Für Anfänger
Niveau A1
TEXT,

                'label' => 'Anzeige D',
                'sort_order' => 4,
                'is_active' => true,
            ]);

            /*
             * Situation 1
             */
            $question1 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'matching',
                'prompt' =>
                'Samir arbeitet jeden Werktag bis 17 Uhr. Er möchte danach einen allgemeinen Deutschkurs besuchen.',
                'points' => 1,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            $this->createOptions($question1, 'A');

            /*
             * Situation 2
             */
            $question2 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'matching',
                'prompt' =>
                'Maria arbeitet von Montag bis Freitag und hat nur am Wochenende Zeit für einen Deutschkurs.',
                'points' => 1,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            $this->createOptions($question2, 'B');

            /*
             * Situation 3
             */
            $question3 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'matching',
                'prompt' =>
                'Olena spricht schon Deutsch auf B1-Niveau. Sie braucht den Kurs besonders für ihre Arbeit und den Kontakt mit Kunden.',
                'points' => 1,
                'sort_order' => 3,
                'is_active' => true,
            ]);

            $this->createOptions($question3, 'C');
        });
    }

    private function createOptions(Question $question, string $correctOption): void
    {
        foreach (['A', 'B', 'C', 'D'] as $index => $option) {
            AnswerOption::create([
                'question_id' => $question->id,
                'text' => 'Anzeige ' . $option,
                'is_correct' => $option === $correctOption,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}

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

class DtzReadingPart4Seeder extends Seeder
{
    /**
     * Seed an original DTZ-style reading exercise for Lesen Teil 4.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $examFormat = ExamFormat::where('key', 'dtz')->firstOrFail();
            $examSection = ExamSection::where('key', 'reading')->firstOrFail();

            $examPart = ExamPart::where('exam_format_id', $examFormat->id)
                ->where('exam_section_id', $examSection->id)
                ->where('key', 'reading_part_4')
                ->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                ['key' => 'dtz-reading-part-4-001'],
                [
                    'exam_format_id' => $examFormat->id,
                    'exam_section_id' => $examSection->id,
                    'exam_part_id' => $examPart->id,
                    'type' => 'true_false',

                    'title' => [
                        'de' => 'Lesen Teil 4 – Aufgabe 1',
                        'ar' => 'القراءة الجزء 4 – التمرين 1',
                        'en' => 'Reading Part 4 – Exercise 1',
                        'tr' => 'Okuma Bölüm 4 – Alıştırma 1',
                        'uk' => 'Читання, частина 4 – Вправа 1',
                    ],

                    'description' => [
                        'de' => 'Lesen Sie die Hausordnung und entscheiden Sie: richtig oder falsch.',
                        'ar' => 'اقرأ قواعد المنزل وحدد: صحيح أم خطأ.',
                        'en' => 'Read the house rules and decide: true or false.',
                        'tr' => 'Apartman kurallarını okuyun ve doğru mu yanlış mı olduğuna karar verin.',
                        'uk' => 'Прочитайте правила будинку та визначте: правильно чи неправильно.',
                    ],

                    'instructions' =>
                    'Lesen Sie den Text. Entscheiden Sie bei jeder Aussage: Richtig oder Falsch.',

                    'difficulty' => 'medium',
                    'access_level' => 'free',
                    'status' => 'published',
                    'sort_order' => 1,
                    'published_at' => now(),
                ]
            );

            // Development seeder: make repeated seeding predictable.
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',
                'label' => 'Hausordnung',
                'content' => <<<'TEXT'
Hausordnung

Liebe Bewohnerinnen und Bewohner,

bitte beachten Sie folgende Regeln in unserem Haus:

1. Ruhezeiten
Von 22:00 Uhr bis 7:00 Uhr sowie zwischen 13:00 Uhr und 15:00 Uhr ist besondere Rücksicht auf die Nachbarn zu nehmen. Laute Musik und andere störende Geräusche sind in dieser Zeit nicht erlaubt.

2. Treppenhaus
Das Treppenhaus und die Flure müssen aus Sicherheitsgründen frei bleiben. Fahrräder, Kinderwagen und andere Gegenstände dürfen dort nicht abgestellt werden. Kinderwagen können im dafür vorgesehenen Raum im Erdgeschoss abgestellt werden.

3. Müll
Hausmüll gehört in die dafür vorgesehenen Mülltonnen im Hof. Glasflaschen dürfen dort nicht entsorgt werden. Dafür stehen öffentliche Glascontainer an der Hauptstraße zur Verfügung.

4. Waschküche
Die Waschküche kann von Montag bis Samstag zwischen 8:00 Uhr und 20:00 Uhr benutzt werden. An Sonn- und Feiertagen bleibt sie geschlossen.

Vielen Dank für Ihre Rücksichtnahme.

Ihre Hausverwaltung
TEXT,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            $this->createTrueFalseQuestion(
                $exercise,
                'Zwischen 13:00 Uhr und 15:00 Uhr soll es im Haus ruhig sein.',
                true,
                1
            );

            $this->createTrueFalseQuestion(
                $exercise,
                'Kinderwagen dürfen im Treppenhaus abgestellt werden.',
                false,
                2
            );

            $this->createTrueFalseQuestion(
                $exercise,
                'Die Waschküche kann auch am Sonntag benutzt werden.',
                false,
                3
            );
        });
    }

    private function createTrueFalseQuestion(
        Exercise $exercise,
        string $prompt,
        bool $correctAnswer,
        int $sortOrder
    ): void {
        $question = Question::create([
            'exercise_id' => $exercise->id,
            'type' => 'true_false',
            'prompt' => $prompt,
            'points' => 1,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);

        AnswerOption::create([
            'question_id' => $question->id,
            'text' => 'Richtig',
            'is_correct' => $correctAnswer,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        AnswerOption::create([
            'question_id' => $question->id,
            'text' => 'Falsch',
            'is_correct' => ! $correctAnswer,
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }
}

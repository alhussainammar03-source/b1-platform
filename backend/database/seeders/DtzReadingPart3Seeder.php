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

class DtzReadingPart3Seeder extends Seeder
{
    /**
     * Seed an original DTZ-style reading exercise for Lesen Teil 3.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $examFormat = ExamFormat::where('key', 'dtz')->firstOrFail();
            $examSection = ExamSection::where('key', 'reading')->firstOrFail();

            $examPart = ExamPart::where('exam_format_id', $examFormat->id)
                ->where('exam_section_id', $examSection->id)
                ->where('key', 'reading_part_3')
                ->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                [
                    'key' => 'dtz-reading-part-3-001',
                ],
                [
                    'exam_format_id' => $examFormat->id,
                    'exam_section_id' => $examSection->id,
                    'exam_part_id' => $examPart->id,

                    'type' => 'mixed',

                    'title' => [
                        'de' => 'Lesen Teil 3 – Aufgabe 1',
                        'ar' => 'القراءة الجزء 3 – التمرين 1',
                        'en' => 'Reading Part 3 – Exercise 1',
                        'tr' => 'Okuma Bölüm 3 – Alıştırma 1',
                        'uk' => 'Читання, частина 3 – Вправа 1',
                    ],

                    'description' => [
                        'de' => 'Lesen Sie die Information und beantworten Sie die Fragen.',
                        'ar' => 'اقرأ المعلومات ثم أجب عن الأسئلة.',
                        'en' => 'Read the information and answer the questions.',
                        'tr' => 'Bilgiyi okuyun ve soruları cevaplayın.',
                        'uk' => 'Прочитайте інформацію та дайте відповіді на запитання.',
                    ],

                    'instructions' =>
                    'Lesen Sie den Text. Wählen Sie bei jeder Aufgabe die richtige Lösung.',

                    'difficulty' => 'medium',
                    'access_level' => 'free',
                    'status' => 'published',
                    'sort_order' => 1,
                    'published_at' => now(),
                ]
            );

            /*
             * Keep the development seeder repeatable.
             *
             * Important: this is fine for development seed data.
             * Published production content should later be versioned instead
             * of deleting questions that may already belong to user attempts.
             */
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',
                'label' => 'Mitteilung der Hausverwaltung',
                'content' => <<<'TEXT'
Liebe Bewohnerinnen und Bewohner,

am kommenden Dienstag werden zwischen 8:00 und 14:00 Uhr Arbeiten an der Heizungsanlage durchgeführt. In dieser Zeit kann die Heizung im gesamten Gebäude nicht benutzt werden.

Bitte sorgen Sie dafür, dass die Mitarbeiter der Firma ThermoFix Zugang zu den Heizkörpern in Ihrer Wohnung haben. Sie müssen dafür nicht den ganzen Tag zu Hause bleiben. Wenn Sie nicht da sein können, geben Sie Ihren Wohnungsschlüssel bitte spätestens am Montag bis 16:00 Uhr im Büro der Hausverwaltung ab.

Wegen der Arbeiten bleibt außerdem der Keller am Dienstag geschlossen. Fahrräder, die Sie an diesem Tag benötigen, holen Sie bitte vorher heraus.

Bei Fragen erreichen Sie die Hausverwaltung unter 0209 555 27 40.

Vielen Dank für Ihr Verständnis.

Ihre Hausverwaltung
TEXT,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            /*
             * Question 1 – Multiple Choice
             */
            $question1 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'multiple_choice',
                'prompt' => 'Warum können die Bewohner am Dienstag die Heizung nicht benutzen?',
                'points' => 1,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            $this->createOptions($question1, [
                ['text' => 'Die Heizungsanlage wird repariert.', 'correct' => true],
                ['text' => 'Das Gebäude bekommt neue Fenster.', 'correct' => false],
                ['text' => 'Die Hausverwaltung ist geschlossen.', 'correct' => false],
            ]);

            /*
             * Question 2 – Multiple Choice
             */
            $question2 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'multiple_choice',
                'prompt' => 'Was sollen Bewohner tun, wenn sie am Dienstag nicht zu Hause sein können?',
                'points' => 1,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            $this->createOptions($question2, [
                [
                    'text' => 'Den Schlüssel rechtzeitig bei der Hausverwaltung abgeben.',
                    'correct' => true,
                ],
                [
                    'text' => 'Einen neuen Termin mit ThermoFix vereinbaren.',
                    'correct' => false,
                ],
                [
                    'text' => 'Den Schlüssel im Keller hinterlegen.',
                    'correct' => false,
                ],
            ]);

            /*
             * Question 3 – True / False
             */
            $question3 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'true_false',
                'prompt' => 'Am Dienstag können die Bewohner ihre Fahrräder jederzeit aus dem Keller holen.',
                'points' => 1,
                'sort_order' => 3,
                'is_active' => true,
            ]);

            $this->createOptions($question3, [
                ['text' => 'Richtig', 'correct' => false],
                ['text' => 'Falsch', 'correct' => true],
            ]);
        });
    }

    /**
     * Create answer options without exposing correctness through the public API.
     */
    private function createOptions(Question $question, array $options): void
    {
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

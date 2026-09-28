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

class DtzReadingPart1Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $format = ExamFormat::where('key', 'dtz')->firstOrFail();

            $section = ExamSection::where('key', 'reading')->firstOrFail();

            $part = ExamPart::where('exam_format_id', $format->id)
                ->where('exam_section_id', $section->id)
                ->where('key', 'reading_part_1')
                ->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                [
                    'key' => 'dtz-reading-part-1-001',
                ],
                [
                    'exam_format_id' => $format->id,
                    'exam_section_id' => $section->id,
                    'exam_part_id' => $part->id,

                    'type' => 'multiple_choice',

                    'title' => [
                        'de' => 'Lesen Teil 1 – Übung 1',
                        'ar' => 'القراءة الجزء 1 – التدريب 1',
                        'en' => 'Reading Part 1 – Exercise 1',
                        'tr' => 'Okuma Bölüm 1 – Alıştırma 1',
                        'uk' => 'Читання, частина 1 – Вправа 1',
                    ],

                    'description' => [
                        'de' => 'Lesen Sie die Information und beantworten Sie die Fragen.',
                        'ar' => 'اقرأ المعلومات ثم أجب عن الأسئلة.',
                        'en' => 'Read the information and answer the questions.',
                        'tr' => 'Bilgiyi okuyun ve soruları cevaplayın.',
                        'uk' => 'Прочитайте інформацію та дайте відповіді на запитання.',
                    ],

                    'instructions' => 'Lesen Sie den Text und wählen Sie die richtige Antwort.',

                    'difficulty' => 'easy',
                    'access_level' => 'free',
                    'status' => 'published',
                    'sort_order' => 1,
                    'published_at' => now(),
                ]
            );

            /*
             * Der Seeder soll mehrfach ausgeführt werden können,
             * ohne Fragen und Texte zu duplizieren.
             */
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'type' => 'text',

                'content' => <<<'TEXT'
Stadtbibliothek Nord

Öffnungszeiten:

Montag: geschlossen
Dienstag bis Freitag: 10:00–18:00 Uhr
Samstag: 10:00–14:00 Uhr
Sonntag: geschlossen

Für die Anmeldung brauchen Sie einen Personalausweis oder Reisepass.

Kinder und Jugendliche unter 18 Jahren bezahlen keine Jahresgebühr.

Adresse:
Parkstraße 24
TEXT,

                'label' => 'Information',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            /*
             * Frage 1
             */
            $question1 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'multiple_choice',
                'prompt' => 'Wann ist die Bibliothek am Samstag geöffnet?',
                'points' => 1,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question1->id,
                'text' => 'Von 10:00 bis 14:00 Uhr.',
                'is_correct' => true,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question1->id,
                'text' => 'Von 10:00 bis 18:00 Uhr.',
                'is_correct' => false,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question1->id,
                'text' => 'Die Bibliothek ist geschlossen.',
                'is_correct' => false,
                'sort_order' => 3,
                'is_active' => true,
            ]);

            /*
             * Frage 2
             */
            $question2 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'multiple_choice',
                'prompt' => 'Was braucht man für die Anmeldung?',
                'points' => 1,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question2->id,
                'text' => 'Einen Personalausweis oder Reisepass.',
                'is_correct' => true,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question2->id,
                'text' => 'Ein Passfoto.',
                'is_correct' => false,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question2->id,
                'text' => 'Eine Arbeitsbescheinigung.',
                'is_correct' => false,
                'sort_order' => 3,
                'is_active' => true,
            ]);

            /*
             * Frage 3
             */
            $question3 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'multiple_choice',
                'prompt' => 'Wer bezahlt keine Jahresgebühr?',
                'points' => 1,
                'sort_order' => 3,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question3->id,
                'text' => 'Alle Besucher.',
                'is_correct' => false,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question3->id,
                'text' => 'Kinder und Jugendliche unter 18 Jahren.',
                'is_correct' => true,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $question3->id,
                'text' => 'Nur Studenten.',
                'is_correct' => false,
                'sort_order' => 3,
                'is_active' => true,
            ]);
        });
    }
}

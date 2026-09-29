<?php

namespace Database\Seeders;

use App\Models\AnswerOption;
use App\Models\ExamFormat;
use App\Models\ExamPart;
use App\Models\ExamSection;
use App\Models\Exercise;
use App\Models\ExerciseStimulus;
use App\Models\MediaFile;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DtzListeningPart1Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $dtz = ExamFormat::where('key', 'dtz')->firstOrFail();
            $listening = ExamSection::where('key', 'listening')->firstOrFail();

            $part = ExamPart::where('exam_format_id', $dtz->id)
                ->where('exam_section_id', $listening->id)
                ->where('key', 'listening_part_1')
                ->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                ['key' => 'dtz-listening-part-1-001'],
                [
                    'exam_format_id' => $dtz->id,
                    'exam_section_id' => $listening->id,
                    'exam_part_id' => $part->id,
                    'type' => 'multiple_choice',
                    'title' => [
                        'de' => 'Hören Teil 1 – Ansagen verstehen',
                        'ar' => 'الاستماع الجزء 1 – فهم الإعلانات الصوتية',
                        'en' => 'Listening Part 1 – Understanding announcements',
                        'tr' => 'Dinleme Bölüm 1 – Duyuruları anlama',
                        'uk' => 'Аудіювання, частина 1 – Розуміння оголошень',
                    ],
                    'description' => [
                        'de' => 'Hören Sie vier kurze Ansagen und beantworten Sie die Fragen.',
                        'ar' => 'استمع إلى أربعة إعلانات قصيرة وأجب عن الأسئلة.',
                        'en' => 'Listen to four short announcements and answer the questions.',
                        'tr' => 'Dört kısa duyuruyu dinleyin ve soruları cevaplayın.',
                        'uk' => 'Прослухайте чотири короткі оголошення та дайте відповіді на запитання.',
                    ],
                    'instructions' => 'Hören Sie die Ansagen und wählen Sie bei jeder Aufgabe die richtige Lösung a, b oder c.',
                    'difficulty' => 'easy',
                    'access_level' => 'free',
                    'status' => 'published',
                    'sort_order' => 1,
                    'published_at' => now(),
                ]
            );

            // Development seeder: rebuild child content on every run.
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            $tasks = [
                [
                    'audio' => [
                        'path' => 'audio/dtz/listening/part1/ansage-1.mp3',
                        'original_name' => 'ansage-1.mp3',
                        'duration_seconds' => 18,
                        'label' => 'Ansage 1',
                    ],
                    'transcript' => 'Achtung, liebe Fahrgäste. Der Regionalzug nach Dortmund fährt heute nicht von Gleis 4, sondern von Gleis 7. Die Abfahrt ist um 10 Uhr 25.',
                    'question' => 'Von welchem Gleis fährt der Zug heute?',
                    'options' => [
                        ['text' => 'Von Gleis 4.', 'correct' => false],
                        ['text' => 'Von Gleis 7.', 'correct' => true],
                        ['text' => 'Von Gleis 10.', 'correct' => false],
                    ],
                ],
                [
                    'audio' => [
                        'path' => 'audio/dtz/listening/part1/ansage-2.mp3',
                        'original_name' => 'ansage-2.mp3',
                        'duration_seconds' => 17,
                        'label' => 'Ansage 2',
                    ],
                    'transcript' => 'Guten Tag. Hier ist die Praxis Dr. Berger. Ihr Termin morgen muss leider verschoben werden. Bitte kommen Sie nicht um neun Uhr, sondern um elf Uhr dreißig.',
                    'question' => 'Wann ist der neue Termin?',
                    'options' => [
                        ['text' => 'Um 9 Uhr.', 'correct' => false],
                        ['text' => 'Um 11 Uhr.', 'correct' => false],
                        ['text' => 'Um 11:30 Uhr.', 'correct' => true],
                    ],
                ],
                [
                    'audio' => [
                        'path' => 'audio/dtz/listening/part1/ansage-3.mp3',
                        'original_name' => 'ansage-3.mp3',
                        'duration_seconds' => 19,
                        'label' => 'Ansage 3',
                    ],
                    'transcript' => 'Liebe Kundinnen und Kunden, unser Supermarkt schließt heute wegen technischer Arbeiten bereits um 18 Uhr. Morgen sind wir wieder wie gewohnt ab 8 Uhr für Sie da.',
                    'question' => 'Wann schließt der Supermarkt heute?',
                    'options' => [
                        ['text' => 'Um 8 Uhr.', 'correct' => false],
                        ['text' => 'Um 18 Uhr.', 'correct' => true],
                        ['text' => 'Um 20 Uhr.', 'correct' => false],
                    ],
                ],
                [
                    'audio' => [
                        'path' => 'audio/dtz/listening/part1/ansage-4.mp3',
                        'original_name' => 'ansage-4.mp3',
                        'duration_seconds' => 20,
                        'label' => 'Ansage 4',
                    ],
                    'transcript' => 'Hallo Frau Yilmaz, hier ist Herr König von der Sprachschule. Der Deutschkurs beginnt nächste Woche nicht am Montag, sondern erst am Mittwoch. Der Unterricht beginnt wie geplant um 17 Uhr.',
                    'question' => 'An welchem Tag beginnt der Deutschkurs?',
                    'options' => [
                        ['text' => 'Am Montag.', 'correct' => false],
                        ['text' => 'Am Mittwoch.', 'correct' => true],
                        ['text' => 'Am Freitag.', 'correct' => false],
                    ],
                ],
            ];

            foreach ($tasks as $index => $task) {
                $media = MediaFile::updateOrCreate(
                    [
                        'disk' => 'public',
                        'path' => $task['audio']['path'],
                    ],
                    [
                        'type' => 'audio',
                        'original_name' => $task['audio']['original_name'],
                        'mime_type' => 'audio/mpeg',
                        'duration_seconds' => $task['audio']['duration_seconds'],
                        'source' => 'Codelva original',
                        'license' => 'proprietary',
                        'meta' => [
                            'purpose' => 'dtz_listening_practice',
                        ],
                    ]
                );

                ExerciseStimulus::create([
                    'exercise_id' => $exercise->id,
                    'media_file_id' => $media->id,
                    'type' => 'audio',
                    'content' => null,
                    'label' => $task['audio']['label'],
                    'reference_description' => $task['transcript'],
                    'meta' => [
                        'transcript_visibility' => 'hidden',
                    ],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);

                $question = Question::create([
                    'exercise_id' => $exercise->id,
                    'type' => 'multiple_choice',
                    'prompt' => $task['question'],
                    'points' => 1,
                    'meta' => [
                        'audio_number' => $index + 1,
                    ],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);

                foreach ($task['options'] as $optionIndex => $option) {
                    AnswerOption::create([
                        'question_id' => $question->id,
                        'text' => $option['text'],
                        'is_correct' => $option['correct'],
                        'sort_order' => $optionIndex + 1,
                        'is_active' => true,
                    ]);
                }
            }
        });
    }
}

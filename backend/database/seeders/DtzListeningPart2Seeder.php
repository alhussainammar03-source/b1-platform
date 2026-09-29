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

class DtzListeningPart2Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $dtz = ExamFormat::where('key', 'dtz')->firstOrFail();
            $listening = ExamSection::where('key', 'listening')->firstOrFail();

            $part = ExamPart::where('exam_format_id', $dtz->id)
                ->where('exam_section_id', $listening->id)
                ->where('key', 'listening_part_2')
                ->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                ['key' => 'dtz-listening-part-2-001'],
                [
                    'exam_format_id' => $dtz->id,
                    'exam_section_id' => $listening->id,
                    'exam_part_id' => $part->id,
                    'type' => 'multiple_choice',

                    'title' => [
                        'de' => 'Hören Teil 2 – Informationen verstehen',
                        'ar' => 'الاستماع الجزء 2 – فهم المعلومات',
                        'en' => 'Listening Part 2 – Understanding information',
                        'tr' => 'Dinleme Bölüm 2 – Bilgileri anlama',
                        'uk' => 'Аудіювання, частина 2 – Розуміння інформації',
                    ],

                    'description' => [
                        'de' => 'Hören Sie fünf kurze Beiträge und beantworten Sie die Fragen.',
                        'ar' => 'استمع إلى خمسة مقاطع قصيرة وأجب عن الأسئلة.',
                        'en' => 'Listen to five short audio clips and answer the questions.',
                        'tr' => 'Beş kısa ses kaydını dinleyin ve soruları cevaplayın.',
                        'uk' => 'Прослухайте п’ять коротких аудіозаписів та дайте відповіді на запитання.',
                    ],

                    'instructions' =>
                    'Hören Sie die Beiträge und wählen Sie bei jeder Aufgabe die richtige Lösung a, b oder c.',

                    'difficulty' => 'easy',
                    'access_level' => 'free',
                    'status' => 'published',
                    'sort_order' => 2,
                    'published_at' => now(),
                ]
            );

            // Development seeder: rebuild child content on every run.
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            $tasks = [

                // Aufgabe 1
                [
                    'audio' => [
                        'path' => 'audio/dtz/listening/part2/beitrag-1.mp3',
                        'original_name' => 'beitrag-1.mp3',
                        'size' => 287485,
                        'duration_seconds' => 16,
                        'label' => 'Beitrag 1',
                    ],

                    'transcript' =>
                    'Und nun eine Information zum Wetter. Am Vormittag bleibt es noch trocken und teilweise sonnig. Ab dem Nachmittag ziehen jedoch dunkle Wolken auf. Besonders im Westen muss mit starkem Regen gerechnet werden. Die Temperaturen erreichen höchstens 17 Grad.',

                    'question' =>
                    'Wie wird das Wetter am Nachmittag?',

                    'options' => [
                        [
                            'text' => 'Es bleibt sonnig und trocken.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Es kann stark regnen.',
                            'correct' => true,
                        ],
                        [
                            'text' => 'Es wird über 25 Grad warm.',
                            'correct' => false,
                        ],
                    ],
                ],

                // Aufgabe 2
                [
                    'audio' => [
                        'path' => 'audio/dtz/listening/part2/beitrag-2.mp3',
                        'original_name' => 'beitrag-2.mp3',
                        'size' => 261572,
                        'duration_seconds' => 15,
                        'label' => 'Beitrag 2',
                    ],

                    'transcript' =>
                    'Wegen Bauarbeiten bleibt die Stadtbibliothek am kommenden Samstag geschlossen. Bücher und andere Medien können an diesem Tag nicht ausgeliehen werden. Die Rückgabebox vor dem Haupteingang steht Ihnen aber wie gewohnt rund um die Uhr zur Verfügung.',

                    'question' =>
                    'Was können Besucher am Samstag machen?',

                    'options' => [
                        [
                            'text' => 'Bücher in der Bibliothek ausleihen.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Die Bibliothek normal besuchen.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Medien über die Rückgabebox zurückgeben.',
                            'correct' => true,
                        ],
                    ],
                ],

                // Aufgabe 3
                [
                    'audio' => [
                        'path' => 'audio/dtz/listening/part2/beitrag-3.mp3',
                        'original_name' => 'beitrag-3.mp3',
                        'size' => 269513,
                        'duration_seconds' => 15,
                        'label' => 'Beitrag 3',
                    ],

                    'transcript' =>
                    'Am Sonntag findet im Stadtpark wieder der beliebte Familienflohmarkt statt. Der Markt beginnt um zehn Uhr und endet um sechzehn Uhr. Wer selbst etwas verkaufen möchte, muss sich vorher online anmelden. Für Besucher ist der Eintritt kostenlos.',

                    'question' =>
                    'Was müssen Verkäufer vor dem Flohmarkt tun?',

                    'options' => [
                        [
                            'text' => 'Sich online anmelden.',
                            'correct' => true,
                        ],
                        [
                            'text' => 'Eintritt bezahlen.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Um 16 Uhr kommen.',
                            'correct' => false,
                        ],
                    ],
                ],

                // Aufgabe 4
                [
                    'audio' => [
                        'path' => 'audio/dtz/listening/part2/beitrag-4.mp3',
                        'original_name' => 'beitrag-4.mp3',
                        'size' => 312981,
                        'duration_seconds' => 18,
                        'label' => 'Beitrag 4',
                    ],

                    'transcript' =>
                    'Aufgrund einer technischen Störung fährt die Straßenbahnlinie 302 zurzeit nur zwischen Hauptbahnhof und Rathaus. Fahrgäste in Richtung Buer benutzen bitte ab Rathaus die Ersatzbusse. Die Verkehrsbetriebe rechnen damit, dass die Störung gegen 14 Uhr behoben ist.',

                    'question' =>
                    'Was sollen Fahrgäste in Richtung Buer tun?',

                    'options' => [
                        [
                            'text' => 'Am Hauptbahnhof warten.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Ab Rathaus einen Ersatzbus nehmen.',
                            'correct' => true,
                        ],
                        [
                            'text' => 'Die Straßenbahnlinie 302 bis Buer nehmen.',
                            'correct' => false,
                        ],
                    ],
                ],

                // Aufgabe 5
                [
                    'audio' => [
                        'path' => 'audio/dtz/listening/part2/beitrag-5.mp3',
                        'original_name' => 'beitrag-5.mp3',
                        'size' => 302532,
                        'duration_seconds' => 17,
                        'label' => 'Beitrag 5',
                    ],

                    'transcript' =>
                    'Das Bürgerbüro informiert: Für die Beantragung eines neuen Personalausweises benötigen Sie ab sofort einen Termin. Diesen können Sie über die Internetseite der Stadt vereinbaren. In dringenden Fällen können Sie montags zwischen acht und zehn Uhr auch ohne Termin kommen.',

                    'question' =>
                    'Wann kann man ohne Termin zum Bürgerbüro kommen?',

                    'options' => [
                        [
                            'text' => 'Jeden Morgen.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Montags zwischen 8 und 10 Uhr.',
                            'correct' => true,
                        ],
                        [
                            'text' => 'Nur am Wochenende.',
                            'correct' => false,
                        ],
                    ],
                ],
            ];

            foreach ($tasks as $index => $task) {

                $media = MediaFile::updateOrCreate(
                    [
                        'path' => $task['audio']['path'],
                    ],
                    [
                        'disk' => 'local',
                        'type' => 'audio',
                        'original_name' => $task['audio']['original_name'],
                        'mime_type' => 'audio/mpeg',
                        'size' => $task['audio']['size'],
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

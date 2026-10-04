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

class DtzListeningPart3Seeder extends Seeder
{
    public function run(): void
    {
        $examFormat = ExamFormat::where('key', 'dtz')->firstOrFail();
        $examSection = ExamSection::where('key', 'listening')->firstOrFail();

        $examPart = ExamPart::where('exam_format_id', $examFormat->id)
            ->where('exam_section_id', $examSection->id)
            ->where('key', 'listening_part_3')
            ->firstOrFail();

        $exercise = Exercise::updateOrCreate(
            [
                'key' => 'dtz-listening-part-3-001',
            ],
            [
                'exam_format_id' => $examFormat->id,
                'exam_section_id' => $examSection->id,
                'exam_part_id' => $examPart->id,

                'type' => 'mixed',

                'title' => [
                    'de' => 'Hören Teil 3 – Gespräche verstehen',
                    'ar' => 'الاستماع الجزء 3 – فهم المحادثات',
                    'en' => 'Listening Part 3 – Understanding conversations',
                    'tr' => 'Dinleme Bölüm 3 – Konuşmaları anlama',
                    'uk' => 'Аудіювання, частина 3 – Розуміння розмов',
                ],

                'description' => [
                    'de' => 'Hören Sie vier Gespräche. Zu jedem Gespräch bearbeiten Sie zwei Aufgaben.',
                    'ar' => 'استمع إلى أربع محادثات. لكل محادثة مهمتان.',
                    'en' => 'Listen to four conversations. Complete two tasks for each conversation.',
                    'tr' => 'Dört konuşmayı dinleyin. Her konuşma için iki görevi cevaplayın.',
                    'uk' => 'Прослухайте чотири розмови. До кожної розмови виконайте два завдання.',
                ],

                'instructions' => 'Hören Sie die Gespräche. Entscheiden Sie zuerst, ob die Aussage richtig oder falsch ist. Wählen Sie danach die richtige Lösung a, b oder c.',

                'difficulty' => 'easy',
                'access_level' => 'free',
                'status' => 'published',
                'sort_order' => 3,
                'published_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Development Seeder
        |--------------------------------------------------------------------------
        | Rebuild the exercise children when this seeder is executed again.
        */

        $exercise->questions()->delete();
        $exercise->stimuli()->delete();

        $tasks = [
            /*
            |--------------------------------------------------------------------------
            | Gespräch 1
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Gespräch 1',
                'path' => 'audio/dtz/listening/part3/gespraech-1.mp3',
                'size' => 343492,
                'duration_seconds' => 20,

                'transcript' => 'Frau: Entschuldigung, ich habe für heute Abend einen Tisch für zwei Personen reserviert. Mein Name ist Schneider. Mann: Einen Moment bitte. Ja, Frau Schneider, Ihre Reservierung ist für 19 Uhr eingetragen. Frau: Ach, ich dachte, ich hätte für 19 Uhr 30 reserviert. Mann: Kein Problem. Wir können den Tisch bis 19 Uhr 30 für Sie freihalten.',

                'true_false' => [
                    'prompt' => 'Frau Schneider hat einen Tisch reserviert.',
                    'correct' => true,
                ],

                'multiple_choice' => [
                    'prompt' => 'Wann möchte Frau Schneider kommen?',
                    'options' => [
                        [
                            'text' => 'Um 18:30 Uhr.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Um 19:00 Uhr.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Um 19:30 Uhr.',
                            'correct' => true,
                        ],
                    ],
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Gespräch 2
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Gespräch 2',
                'path' => 'audio/dtz/listening/part3/gespraech-2.mp3',
                'size' => 295008,
                'duration_seconds' => 17,

                'transcript' => 'Mann: Hallo Anna, kommst du morgen mit dem Auto zur Arbeit? Frau: Nein, mein Auto ist noch in der Werkstatt. Ich wollte eigentlich mit dem Bus fahren. Mann: Morgen fahren wegen des Streiks aber keine Busse. Frau: Wirklich? Dann nehme ich die Straßenbahn. Die Linie 107 fährt direkt bis zum Büro.',

                'true_false' => [
                    'prompt' => 'Annas Auto ist noch in der Werkstatt.',
                    'correct' => true,
                ],

                'multiple_choice' => [
                    'prompt' => 'Wie fährt Anna morgen zur Arbeit?',
                    'options' => [
                        [
                            'text' => 'Mit dem Bus.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Mit der Straßenbahn.',
                            'correct' => true,
                        ],
                        [
                            'text' => 'Mit dem Auto.',
                            'correct' => false,
                        ],
                    ],
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Gespräch 3
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Gespräch 3',
                'path' => 'audio/dtz/listening/part3/gespraech-3.mp3',
                'size' => 279544,
                'duration_seconds' => 16,

                'transcript' => 'Frau: Guten Tag, ich möchte dieses Hemd gern umtauschen. Es ist leider zu klein. Mann: Haben Sie den Kassenbon dabei? Frau: Ja, hier bitte. Gibt es das Hemd auch in Größe L? Mann: Leider nicht mehr in Blau. Wir haben es aber noch in Schwarz und Weiß. Frau: Dann nehme ich es in Schwarz.',

                'true_false' => [
                    'prompt' => 'Die Frau möchte das Hemd umtauschen.',
                    'correct' => true,
                ],

                'multiple_choice' => [
                    'prompt' => 'Welche Farbe wählt die Frau?',
                    'options' => [
                        [
                            'text' => 'Blau.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Weiß.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Schwarz.',
                            'correct' => true,
                        ],
                    ],
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Gespräch 4
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Gespräch 4',
                'path' => 'audio/dtz/listening/part3/gespraech-4.mp3',
                'size' => 267005,
                'duration_seconds' => 15,

                'transcript' => 'Mann: Guten Morgen, Frau Becker. Ich wollte fragen, ob Sie am Freitag länger arbeiten können. Frau: Am Freitag geht es leider nicht. Da habe ich am Nachmittag einen Arzttermin. Am Donnerstag könnte ich aber bis 19 Uhr bleiben. Mann: Das wäre auch gut. Dann machen wir es so.',

                'true_false' => [
                    'prompt' => 'Frau Becker kann am Freitag länger arbeiten.',
                    'correct' => false,
                ],

                'multiple_choice' => [
                    'prompt' => 'Wann kann Frau Becker länger arbeiten?',
                    'options' => [
                        [
                            'text' => 'Am Mittwoch.',
                            'correct' => false,
                        ],
                        [
                            'text' => 'Am Donnerstag.',
                            'correct' => true,
                        ],
                        [
                            'text' => 'Am Freitag.',
                            'correct' => false,
                        ],
                    ],
                ],
            ],
        ];

        foreach ($tasks as $index => $task) {
            /*
            |--------------------------------------------------------------------------
            | Audio
            |--------------------------------------------------------------------------
            */

            $media = MediaFile::updateOrCreate(
                [
                    'path' => $task['path'],
                ],
                [
                    'type' => 'audio',
                    'disk' => 'local',
                    'original_name' => basename($task['path']),
                    'mime_type' => 'audio/mpeg',

                    'size' => $task['size'],
                    'duration_seconds' => $task['duration_seconds'],

                    'source' => 'Codelva original',
                    'license' => 'proprietary',

                    'meta' => [
                        'purpose' => 'dtz_listening_practice',
                    ],
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Audio Stimulus
            |--------------------------------------------------------------------------
            */

            $stimulus = ExerciseStimulus::create([
                'exercise_id' => $exercise->id,
                'media_file_id' => $media->id,
                'type' => 'audio',
                'content' => null,
                'label' => $task['label'],

                /*
                 * Internal transcript.
                 * This must never be exposed through the public exercise API.
                 */
                'reference_description' => $task['transcript'],

                'meta' => [
                    'transcript_visibility' => 'hidden',
                ],

                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Question 1 – Richtig / Falsch
            |--------------------------------------------------------------------------
            */

            $trueFalseQuestion = Question::create([
                'exercise_id' => $exercise->id,
                'exercise_stimulus_id' => $stimulus->id,
                'type' => 'true_false',
                'prompt' => $task['true_false']['prompt'],
                'instructions' => null,
                'points' => 1,

                'meta' => [
                    'audio_number' => $index + 1,
                    'task_number' => ($index * 2) + 1,
                ],

                'sort_order' => ($index * 2) + 1,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $trueFalseQuestion->id,
                'text' => 'Richtig',
                'is_correct' => $task['true_false']['correct'] === true,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            AnswerOption::create([
                'question_id' => $trueFalseQuestion->id,
                'text' => 'Falsch',
                'is_correct' => $task['true_false']['correct'] === false,
                'sort_order' => 2,
                'is_active' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Question 2 – Multiple Choice
            |--------------------------------------------------------------------------
            */

            $multipleChoiceQuestion = Question::create([
                'exercise_id' => $exercise->id,
                'exercise_stimulus_id' => $stimulus->id,
                'type' => 'multiple_choice',
                'prompt' => $task['multiple_choice']['prompt'],
                'instructions' => null,
                'points' => 1,

                'meta' => [
                    'audio_number' => $index + 1,
                    'task_number' => ($index * 2) + 2,
                ],

                'sort_order' => ($index * 2) + 2,
                'is_active' => true,
            ]);

            foreach (
                $task['multiple_choice']['options']
                as $optionIndex => $option
            ) {
                AnswerOption::create([
                    'question_id' => $multipleChoiceQuestion->id,
                    'text' => $option['text'],
                    'is_correct' => $option['correct'],
                    'sort_order' => $optionIndex + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}

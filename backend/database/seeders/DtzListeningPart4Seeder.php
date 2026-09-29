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

class DtzListeningPart4Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $format = ExamFormat::where('key', 'dtz')->firstOrFail();

            $section = ExamSection::where('key', 'listening')->firstOrFail();

            $part = ExamPart::where('exam_format_id', $format->id)
                ->where('exam_section_id', $section->id)
                ->where('key', 'listening_part_4')
                ->firstOrFail();

            $exercise = Exercise::updateOrCreate(
                [
                    'key' => 'dtz-listening-part-4-001',
                ],
                [
                    'exam_format_id' => $format->id,
                    'exam_section_id' => $section->id,
                    'exam_part_id' => $part->id,

                    'type' => 'matching',

                    'title' => [
                        'de' => 'Hören Teil 4 – Übung 1',
                        'ar' => 'الاستماع الجزء 4 – التدريب 1',
                        'en' => 'Listening Part 4 – Exercise 1',
                        'tr' => 'Dinleme Bölüm 4 – Alıştırma 1',
                        'uk' => 'Аудіювання, частина 4 – Вправа 1',
                    ],

                    'description' => [
                        'de' => 'Hören Sie verschiedene Personen zum Thema Freizeit und Wochenende und ordnen Sie die passenden Aussagen zu.',
                        'ar' => 'استمع إلى أشخاص مختلفين يتحدثون عن وقت الفراغ وعطلة نهاية الأسبوع، ثم اختر العبارة المناسبة.',
                        'en' => 'Listen to different people talking about leisure time and the weekend and match the correct statements.',
                        'tr' => 'Boş zaman ve hafta sonu hakkında konuşan farklı kişileri dinleyin ve uygun ifadeleri eşleştirin.',
                        'uk' => 'Послухайте різних людей, які говорять про дозвілля та вихідні, і доберіть відповідні твердження.',
                    ],

                    'instructions' =>
                    'Hören Sie die Aussagen. Wählen Sie für jede Aufgabe die passende Person.',

                    'difficulty' => 'easy',
                    'access_level' => 'free',
                    'status' => 'published',
                    'sort_order' => 4,
                    'published_at' => now(),
                ]
            );

            /*
             * Seeder mehrfach ausführbar machen.
             */
            $exercise->questions()->delete();
            $exercise->stimuli()->delete();

            /*
             * Audio-Dateien und Transkripte.
             * Die Transkripte sind nur intern und werden
             * nicht über die öffentliche ExerciseResource ausgegeben.
             */
            $speakers = [
                [
                    'number' => 1,
                    'path' => 'audio/dtz/listening/part4/sprecher-1.mp3',
                    'size' => 273274,
                    'duration_seconds' => 16,
                    'transcript' =>
                    'Am Wochenende möchte ich mich vor allem erholen. Unter der Woche arbeite ich viel und komme oft spät nach Hause. Deshalb schlafe ich samstags gern länger und frühstücke in Ruhe. Wenn das Wetter schön ist, gehe ich später noch eine Runde im Park spazieren.',
                ],
                [
                    'number' => 2,
                    'path' => 'audio/dtz/listening/part4/sprecher-2.mp3',
                    'size' => 256556,
                    'duration_seconds' => 14,
                    'transcript' =>
                    'Für mich ist das Wochenende die beste Zeit für Freunde. Wir treffen uns meistens am Samstagabend. Manchmal gehen wir zusammen essen, manchmal kochen wir bei jemandem zu Hause. Wichtig ist für mich eigentlich nur, dass wir Zeit miteinander verbringen.',
                ],
                [
                    'number' => 3,
                    'path' => 'audio/dtz/listening/part4/sprecher-3.mp3',
                    'size' => 251541,
                    'duration_seconds' => 14,
                    'transcript' =>
                    'Ich bin am Wochenende gern aktiv. Am Samstag fahre ich oft mit dem Fahrrad oder gehe schwimmen. Den ganzen Tag zu Hause zu sitzen, ist nichts für mich. Nach dem Sport fühle ich mich viel besser und habe wieder Energie für die neue Woche.',
                ],
                [
                    'number' => 4,
                    'path' => 'audio/dtz/listening/part4/sprecher-4.mp3',
                    'size' => 274528,
                    'duration_seconds' => 16,
                    'transcript' =>
                    'Mein Wochenende gehört meistens meiner Familie. Meine Eltern wohnen nicht weit von uns entfernt, deshalb besuchen wir sie oft am Sonntag. Wir essen gemeinsam zu Mittag und trinken danach Kaffee. Meine Kinder freuen sich immer besonders auf ihre Großeltern.',
                ],
                [
                    'number' => 5,
                    'path' => 'audio/dtz/listening/part4/sprecher-5.mp3',
                    'size' => 278290,
                    'duration_seconds' => 16,
                    'transcript' =>
                    'Am Wochenende erledige ich viele Dinge, für die ich unter der Woche keine Zeit habe. Ich kaufe ein, putze die Wohnung und mache die Wäsche. Wenn alles fertig ist, sehe ich am Abend gern einen Film. So kann ich am Montag entspannt in die neue Woche starten.',
                ],
                [
                    'number' => 6,
                    'path' => 'audio/dtz/listening/part4/sprecher-6.mp3',
                    'size' => 273274,
                    'duration_seconds' => 16,
                    'transcript' =>
                    'Ich versuche, am Wochenende etwas Neues zu unternehmen. Ich besuche zum Beispiel gern eine andere Stadt, ein Museum oder eine Veranstaltung. Dafür muss man nicht weit reisen. Auch in der eigenen Umgebung gibt es viele interessante Orte, die man noch nicht kennt.',
                ],
            ];

            foreach ($speakers as $speaker) {

                $media = MediaFile::updateOrCreate(
                    [
                        'path' => $speaker['path'],
                    ],
                    [
                        'type' => 'audio',
                        'disk' => 'local',
                        'original_name' => basename($speaker['path']),
                        'mime_type' => 'audio/mpeg',
                        'size' => $speaker['size'],
                        'duration_seconds' => $speaker['duration_seconds'],

                        'source' => 'Codelva Original',
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
                    'label' => 'Sprecher ' . $speaker['number'],

                    'reference_description' => $speaker['transcript'],

                    'meta' => [
                        'transcript_visibility' => 'hidden',
                        'speaker_number' => $speaker['number'],
                    ],

                    'sort_order' => $speaker['number'],
                    'is_active' => true,
                ]);
            }

            /*
             * Aufgabe 1
             */
            $question1 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'matching',
                'prompt' =>
                'Wer verbringt das Wochenende besonders gern mit Freunden?',
                'points' => 1,

                'meta' => [
                    'task_number' => 1,
                ],

                'sort_order' => 1,
                'is_active' => true,
            ]);

            $this->createOptions($question1, 2);

            /*
             * Aufgabe 2
             */
            $question2 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'matching',
                'prompt' =>
                'Wer nutzt das Wochenende gern für Sport und Bewegung?',
                'points' => 1,

                'meta' => [
                    'task_number' => 2,
                ],

                'sort_order' => 2,
                'is_active' => true,
            ]);

            $this->createOptions($question2, 3);

            /*
             * Aufgabe 3
             */
            $question3 = Question::create([
                'exercise_id' => $exercise->id,
                'type' => 'matching',
                'prompt' =>
                'Wer entdeckt am Wochenende gern neue Orte und Veranstaltungen?',
                'points' => 1,

                'meta' => [
                    'task_number' => 3,
                ],

                'sort_order' => 3,
                'is_active' => true,
            ]);

            $this->createOptions($question3, 6);
        });
    }

    private function createOptions(
        Question $question,
        int $correctSpeaker
    ): void {
        foreach (range(1, 6) as $index => $speakerNumber) {

            AnswerOption::create([
                'question_id' => $question->id,
                'text' => 'Sprecher ' . $speakerNumber,
                'is_correct' => $speakerNumber === $correctSpeaker,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}

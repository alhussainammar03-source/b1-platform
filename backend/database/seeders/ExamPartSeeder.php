<?php

namespace Database\Seeders;

use App\Models\ExamFormat;
use App\Models\ExamPart;
use App\Models\ExamSection;
use Illuminate\Database\Seeder;

class ExamPartSeeder extends Seeder
{
    public function run(): void
    {
        $dtz = ExamFormat::where('key', 'dtz')->firstOrFail();

        $speaking = ExamSection::where('key', 'speaking')->firstOrFail();
        $reading = ExamSection::where('key', 'reading')->firstOrFail();






        $parts = [
            [
                'key' => 'introduction',
                'task_kind' => 'speaking_intro',

                'title' => [
                    'de' => 'Sich vorstellen',
                    'ar' => 'التعريف عن النفس',
                    'en' => 'Introducing yourself',
                    'tr' => 'Kendini tanıtma',
                    'uk' => 'Представлення себе',
                ],

                'label' => [
                    'de' => 'Teil 1',
                    'ar' => 'الجزء 1',
                    'en' => 'Part 1',
                    'tr' => 'Bölüm 1',
                    'uk' => 'Частина 1',
                ],

                'default_prep_seconds' => null,
                'default_speak_seconds' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],

            [
                'key' => 'photo_description',
                'task_kind' => 'photo_description',

                'title' => [
                    'de' => 'Über ein Thema sprechen',
                    'ar' => 'التحدث عن موضوع',
                    'en' => 'Speaking about a topic',
                    'tr' => 'Bir konu hakkında konuşma',
                    'uk' => 'Розмова на тему',
                ],

                'label' => [
                    'de' => 'Teil 2',
                    'ar' => 'الجزء 2',
                    'en' => 'Part 2',
                    'tr' => 'Bölüm 2',
                    'uk' => 'Частина 2',
                ],

                'default_prep_seconds' => null,
                'default_speak_seconds' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],

            [
                'key' => 'planning',
                'task_kind' => 'planning',

                'title' => [
                    'de' => 'Gemeinsam etwas planen',
                    'ar' => 'التخطيط لشيء معًا',
                    'en' => 'Planning something together',
                    'tr' => 'Birlikte bir şey planlama',
                    'uk' => 'Спільне планування',
                ],

                'label' => [
                    'de' => 'Teil 3',
                    'ar' => 'الجزء 3',
                    'en' => 'Part 3',
                    'tr' => 'Bölüm 3',
                    'uk' => 'Частина 3',
                ],

                'default_prep_seconds' => null,
                'default_speak_seconds' => null,
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($parts as $part) {
            ExamPart::updateOrCreate(
                [
                    'exam_format_id' => $dtz->id,
                    'exam_section_id' => $speaking->id,
                    'key' => $part['key'],
                ],
                $part
            );
        }


        $readingParts = [
            [
                'key' => 'reading_part_1',
                'task_kind' => 'multiple_choice',
                'title' => [
                    'de' => 'Lesen Teil 1',
                    'ar' => 'القراءة - الجزء 1',
                    'en' => 'Reading Part 1',
                    'tr' => 'Okuma Bölüm 1',
                    'uk' => 'Читання, частина 1',
                ],
                'label' => [
                    'de' => 'Teil 1',
                    'ar' => 'الجزء 1',
                    'en' => 'Part 1',
                    'tr' => 'Bölüm 1',
                    'uk' => 'Частина 1',
                ],
                'default_prep_seconds' => null,
                'default_speak_seconds' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'reading_part_2',
                'task_kind' => 'matching',
                'title' => [
                    'de' => 'Lesen Teil 2',
                    'ar' => 'القراءة - الجزء 2',
                    'en' => 'Reading Part 2',
                    'tr' => 'Okuma Bölüm 2',
                    'uk' => 'Читання, частина 2',
                ],
                'label' => [
                    'de' => 'Teil 2',
                    'ar' => 'الجزء 2',
                    'en' => 'Part 2',
                    'tr' => 'Bölüm 2',
                    'uk' => 'Частина 2',
                ],
                'default_prep_seconds' => null,
                'default_speak_seconds' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'key' => 'reading_part_3',
                'task_kind' => 'mixed',
                'title' => [
                    'de' => 'Lesen Teil 3',
                    'ar' => 'القراءة - الجزء 3',
                    'en' => 'Reading Part 3',
                    'tr' => 'Okuma Bölüm 3',
                    'uk' => 'Читання, частина 3',
                ],
                'label' => [
                    'de' => 'Teil 3',
                    'ar' => 'الجزء 3',
                    'en' => 'Part 3',
                    'tr' => 'Bölüm 3',
                    'uk' => 'Частина 3',
                ],
                'default_prep_seconds' => null,
                'default_speak_seconds' => null,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'key' => 'reading_part_4',
                'task_kind' => 'true_false',
                'title' => [
                    'de' => 'Lesen Teil 4',
                    'ar' => 'القراءة - الجزء 4',
                    'en' => 'Reading Part 4',
                    'tr' => 'Okuma Bölüm 4',
                    'uk' => 'Читання, частина 4',
                ],
                'label' => [
                    'de' => 'Teil 4',
                    'ar' => 'الجزء 4',
                    'en' => 'Part 4',
                    'tr' => 'Bölüm 4',
                    'uk' => 'Частина 4',
                ],
                'default_prep_seconds' => null,
                'default_speak_seconds' => null,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'key' => 'reading_part_5',
                'task_kind' => 'gap_fill',
                'title' => [
                    'de' => 'Lesen Teil 5',
                    'ar' => 'القراءة - الجزء 5',
                    'en' => 'Reading Part 5',
                    'tr' => 'Okuma Bölüm 5',
                    'uk' => 'Читання, частина 5',
                ],
                'label' => [
                    'de' => 'Teil 5',
                    'ar' => 'الجزء 5',
                    'en' => 'Part 5',
                    'tr' => 'Bölüm 5',
                    'uk' => 'Частина 5',
                ],
                'default_prep_seconds' => null,
                'default_speak_seconds' => null,
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($readingParts as $part) {
            ExamPart::updateOrCreate(
                [
                    'exam_format_id' => $dtz->id,
                    'exam_section_id' => $reading->id,
                    'key' => $part['key'],
                ],
                $part
            );
        }
    
    
    
        }
}

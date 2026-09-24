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
    }
}

<?php

namespace Database\Seeders;

use App\Models\ExamFormat;
use Illuminate\Database\Seeder;

class ExamFormatSeeder extends Seeder
{
    public function run(): void
    {
        ExamFormat::updateOrCreate(
            ['key' => 'dtz'],
            [
                'name' => [
                    'de' => 'Deutsch-Test für Zuwanderer (DTZ)',
                    'ar' => 'اختبار اللغة الألمانية للمهاجرين (DTZ)',
                    'en' => 'German Test for Immigrants (DTZ)',
                    'tr' => 'Göçmenler için Almanca Testi (DTZ)',
                    'uk' => 'Тест з німецької мови для іммігрантів (DTZ)',
                ],

                'description' => [
                    'de' => 'Vorbereitung auf den Deutsch-Test für Zuwanderer.',
                    'ar' => 'التحضير لاختبار اللغة الألمانية للمهاجرين.',
                    'en' => 'Preparation for the German Test for Immigrants.',
                    'tr' => 'Göçmenler için Almanca Testine hazırlık.',
                    'uk' => 'Підготовка до тесту з німецької мови для іммігрантів.',
                ],

                'level' => 'A2-B1',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }
}

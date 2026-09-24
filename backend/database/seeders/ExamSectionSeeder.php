<?php

namespace Database\Seeders;

use App\Models\ExamSection;
use Illuminate\Database\Seeder;

class ExamSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'key' => 'reading',
                'name' => [
                    'de' => 'Lesen',
                    'ar' => 'القراءة',
                    'en' => 'Reading',
                    'tr' => 'Okuma',
                    'uk' => 'Читання',
                ],
                'description' => [
                    'de' => 'Trainiere dein Leseverstehen für die B1-Prüfung.',
                    'ar' => 'تدرّب على فهم المقروء لامتحان B1.',
                    'en' => 'Practice your reading comprehension for the B1 exam.',
                    'tr' => 'B1 sınavı için okuduğunu anlama becerini geliştir.',
                    'uk' => 'Тренуйте розуміння прочитаного для іспиту B1.',
                ],
                'icon' => 'book-open',
                'sort_order' => 1,
                'is_active' => true,
            ],

            [
                'key' => 'listening',
                'name' => [
                    'de' => 'Hören',
                    'ar' => 'الاستماع',
                    'en' => 'Listening',
                    'tr' => 'Dinleme',
                    'uk' => 'Аудіювання',
                ],
                'description' => [
                    'de' => 'Trainiere dein Hörverstehen für die B1-Prüfung.',
                    'ar' => 'تدرّب على فهم المسموع لامتحان B1.',
                    'en' => 'Practice your listening comprehension for the B1 exam.',
                    'tr' => 'B1 sınavı için dinlediğini anlama becerini geliştir.',
                    'uk' => 'Тренуйте розуміння на слух для іспиту B1.',
                ],
                'icon' => 'headphones',
                'sort_order' => 2,
                'is_active' => true,
            ],

            [
                'key' => 'writing',
                'name' => [
                    'de' => 'Schreiben',
                    'ar' => 'الكتابة',
                    'en' => 'Writing',
                    'tr' => 'Yazma',
                    'uk' => 'Письмо',
                ],
                'description' => [
                    'de' => 'Trainiere schriftliche Aufgaben für die B1-Prüfung.',
                    'ar' => 'تدرّب على المهام الكتابية لامتحان B1.',
                    'en' => 'Practice writing tasks for the B1 exam.',
                    'tr' => 'B1 sınavı için yazma görevlerini çalış.',
                    'uk' => 'Тренуйте письмові завдання для іспиту B1.',
                ],
                'icon' => 'pen-line',
                'sort_order' => 3,
                'is_active' => true,
            ],

            [
                'key' => 'speaking',
                'name' => [
                    'de' => 'Sprechen',
                    'ar' => 'المحادثة',
                    'en' => 'Speaking',
                    'tr' => 'Konuşma',
                    'uk' => 'Говоріння',
                ],
                'description' => [
                    'de' => 'Trainiere die mündlichen Aufgaben für die B1-Prüfung.',
                    'ar' => 'تدرّب على المهام الشفوية لامتحان B1.',
                    'en' => 'Practice speaking tasks for the B1 exam.',
                    'tr' => 'B1 sınavı için konuşma görevlerini çalış.',
                    'uk' => 'Тренуйте усні завдання для іспиту B1.',
                ],
                'icon' => 'mic',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($sections as $section) {
            ExamSection::updateOrCreate(
                ['key' => $section['key']],
                $section
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
                'direction' => 'ltr',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'tr',
                'name' => 'Turkish',
                'native_name' => 'Türkçe',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'uk',
                'name' => 'Ukrainian',
                'native_name' => 'Українська',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}

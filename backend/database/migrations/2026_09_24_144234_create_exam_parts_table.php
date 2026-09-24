<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_parts', function (Blueprint $table) {
            $table->id();

            // نوع الامتحان: DTZ / telc / Goethe ...
            $table->foreignId('exam_format_id')
                ->constrained('exam_formats')
                ->cascadeOnDelete();

            // القسم: Lesen / Hören / Schreiben / Sprechen
            $table->foreignId('exam_section_id')
                ->constrained('exam_sections')
                ->cascadeOnDelete();

            // مثال: introduction, photo_description, planning
            $table->string('key', 100);

            // نوع المهمة، مثال: speaking_intro
            $table->string('task_kind', 100);

            // الاسم الظاهر للمستخدم بعدة لغات
            $table->json('title');

            // مثل: Teil 1
            $table->json('label')->nullable();

            // أوقات افتراضية بالثواني
            $table->unsignedInteger('default_prep_seconds')
                ->nullable();

            $table->unsignedInteger('default_speak_seconds')
                ->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // لا يمكن تكرار نفس الجزء داخل نفس Format + Section
            $table->unique(
                ['exam_format_id', 'exam_section_id', 'key'],
                'exam_parts_format_section_key_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_parts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_stimuli', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            // اختياري: إذا كان Stimulus عبارة عن صورة/صوت/ملف
            $table->foreignId('media_file_id')
                ->nullable()
                ->constrained('media_files')
                ->nullOnDelete();

            // text, image, audio, document
            $table->string('type', 50);

            // للنصوص الألمانية مثل رسالة، إعلان، نص قراءة...
            $table->longText('content')->nullable();

            // عنوان/Label اختياري
            $table->string('label')->nullable();

            // وصف مرجعي للإدارة فقط، مهم خصوصًا لصور Sprechen
            $table->text('reference_description')->nullable();

            // إعدادات إضافية حسب نوع الـStimulus
            $table->json('meta')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'exercise_id',
                'type',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_stimuli');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_exercises', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            // ترتيب التمرين داخل Modellprüfung
            $table->unsignedInteger('sort_order')->default(0);

            // إذا كان إلزاميًا داخل الامتحان
            $table->boolean('is_required')->default(true);

            // إعدادات خاصة بهذا التمرين داخل هذا الامتحان
            // مثال: time_limit_seconds, play_limit ...
            $table->json('settings')->nullable();

            $table->timestamps();

            // نفس التمرين لا يتكرر مرتين في نفس الامتحان
            $table->unique([
                'exam_id',
                'exercise_id',
            ]);

            $table->index([
                'exam_id',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_exercises');
    }
};

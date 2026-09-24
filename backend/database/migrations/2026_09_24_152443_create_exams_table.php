<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_format_id')
                ->constrained('exam_formats')
                ->cascadeOnDelete();

            // modellpruefung-1, modellpruefung-2 ...
            $table->string('key')->unique();

            // Multilingual UI title
            $table->json('title');

            // Multilingual description
            $table->json('description')->nullable();

            // practice = يمكن التدريب بحرية
            // simulation = محاكاة امتحان كامل
            $table->string('mode', 30)->default('simulation');

            // free / premium
            $table->string('access_level', 30)->default('premium');

            // draft / published / archived
            $table->string('status', 30)->default('draft');

            // مدة الامتحان إذا أردنا Timer عام
            $table->unsignedInteger('duration_minutes')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamp('published_at')->nullable();

            // إعدادات إضافية مستقبلًا
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index([
                'exam_format_id',
                'status',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};

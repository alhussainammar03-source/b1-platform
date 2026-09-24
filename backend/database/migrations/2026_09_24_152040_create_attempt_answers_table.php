<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attempt_id')
                ->constrained('attempts')
                ->cascadeOnDelete();

            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();

            // Multiple Choice / Richtig-Falsch
            $table->foreignId('answer_option_id')
                ->nullable()
                ->constrained('answer_options')
                ->nullOnDelete();

            // Schreiben أو إجابة نصية
            $table->longText('text_answer')->nullable();

            // Sprechen: التسجيل الصوتي
            $table->foreignId('media_file_id')
                ->nullable()
                ->constrained('media_files')
                ->nullOnDelete();

            // نتيجة هذا السؤال
            $table->decimal('awarded_points', 6, 2)->nullable();

            // null = لم يتم التصحيح بعد
            $table->boolean('is_correct')->nullable();

            // للتصحيح اليدوي أو AI مستقبلًا
            $table->text('feedback')->nullable();

            // بيانات إضافية:
            // AI score, transcript, selected matching values...
            $table->json('meta')->nullable();

            $table->timestamp('answered_at')->nullable();

            $table->timestamps();

            // إجابة واحدة لكل سؤال داخل المحاولة
            $table->unique([
                'attempt_id',
                'question_id',
            ]);

            $table->index([
                'attempt_id',
                'is_correct',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnDelete();

            // in_progress, submitted, graded, abandoned
            $table->string('status', 30)->default('in_progress');

            // النتيجة النهائية للامتحان
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();

            // أوقات الامتحان
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();

            // آخر نشاط، مهم للاستكمال لاحقًا
            $table->timestamp('last_activity_at')->nullable();

            // معلومات إضافية مستقبلية
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'exam_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};

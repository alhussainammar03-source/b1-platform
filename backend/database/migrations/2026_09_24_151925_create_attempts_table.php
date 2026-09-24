<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            // in_progress, submitted, graded, abandoned
            $table->string('status', 30)->default('in_progress');

            // النتيجة
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();

            // الوقت
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();

            // معلومات إضافية مستقبلية
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'exercise_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};

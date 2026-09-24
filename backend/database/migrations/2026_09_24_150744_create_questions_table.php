<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            // multiple_choice, true_false, matching,
            // gap_fill, writing_text, speaking_response ...
            $table->string('type', 100);

            // نص السؤال نفسه بالألمانية
            $table->text('prompt');

            // تعليمات إضافية اختيارية
            $table->text('instructions')->nullable();

            // عدد النقاط الممكنة
            $table->decimal('points', 6, 2)->default(1);

            // بيانات إضافية خاصة بنوع السؤال عند الحاجة
            $table->json('meta')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'exercise_id',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

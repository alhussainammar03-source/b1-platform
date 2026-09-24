<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            // Optional: item can belong to a specific question
            $table->foreignId('question_id')
                ->nullable()
                ->constrained('questions')
                ->cascadeOnDelete();

            // guiding_question, planning_point,
            // useful_phrase, vocabulary, example_line
            $table->string('kind', 50);

            // German exam/practice content
            $table->text('text');

            // Optional translations/explanations for learning mode
            $table->json('translation')->nullable();

            // Flexible additional configuration
            $table->json('meta')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'exercise_id',
                'kind',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_items');
    }
};

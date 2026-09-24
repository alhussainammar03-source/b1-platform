<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_format_id')
                ->constrained('exam_formats')
                ->cascadeOnDelete();

            $table->foreignId('exam_section_id')
                ->constrained('exam_sections')
                ->cascadeOnDelete();

            $table->foreignId('exam_part_id')
                ->nullable()
                ->constrained('exam_parts')
                ->nullOnDelete();

            // Internal stable identifier
            $table->string('key', 150)->unique();

            // e.g. multiple_choice, true_false, writing,
            // speaking_intro, photo_description, planning
            $table->string('type', 100);

            // User-facing UI metadata
            $table->json('title');
            $table->json('description')->nullable();

            // German exam/task instructions stay German
            $table->text('instructions')->nullable();

            // Difficulty can later support filtering/random practice
            $table->enum('difficulty', [
                'easy',
                'medium',
                'hard',
            ])->default('medium');

            // Access level
            $table->enum('access_level', [
                'free',
                'premium',
            ])->default('premium');

            // Content lifecycle
            $table->enum('status', [
                'draft',
                'published',
                'archived',
            ])->default('draft');

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index([
                'exam_format_id',
                'exam_section_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('speaking_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('exam_format_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('exam_part_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('mode')->default('practice');

            $table->string('status')->default('in_progress');

            $table->unsignedInteger('current_part')->default(1);

            $table->json('covered_topics')->nullable();

            $table->json('meta')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speaking_sessions');
    }
};

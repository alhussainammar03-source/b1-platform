<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('question_id')
                ->constrained()
                ->cascadeOnDelete();

            // text | handwritten_image
            $table->string('input_method', 30);

            // Wenn der Benutzer direkt im Browser schreibt
            $table->longText('original_text')->nullable();

            // Privater Pfad zum hochgeladenen Foto
            $table->string('image_disk')->nullable();
            $table->string('image_path')->nullable();

            // Von OCR / Vision erkannter Text
            $table->longText('extracted_text')->nullable();

            // Text nach Bestätigung/Korrektur durch den Benutzer
            $table->longText('confirmed_text')->nullable();

            /*
             * draft
             * extracting
             * awaiting_confirmation
             * ready_for_evaluation
             * evaluating
             * evaluated
             * failed
             */
            $table->string('status', 40)->default('draft');

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('evaluated_at')->nullable();

            // Technische Zusatzinformationen, aber kein AI-Ergebnis
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['exercise_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_submissions');
    }
};

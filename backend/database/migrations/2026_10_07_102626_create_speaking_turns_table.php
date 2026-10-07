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
        Schema::create('speaking_turns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('speaking_session_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('exam_part_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('question_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // ai = الممتحن / الشريك
            // user = المشارك
            $table->string('speaker');

            // النص الذي قاله الـAI أو النص المستخرج من صوت المستخدم
            $table->text('text')->nullable();

            // مكان ملف الصوت الخاص بهذا الدور
            $table->string('audio_path')->nullable();

            // مدة الصوت بالميلي ثانية
            $table->unsignedInteger('audio_duration_ms')->nullable();

            // ترتيب الحوار داخل الجلسة
            $table->unsignedInteger('turn_number');

            // نوع الدور:
            // greeting, instruction, answer, follow_up,
            // transition, planning_reply ...
            $table->string('turn_type')->nullable();

            // معلومات التحليل:
            // المواضيع المكتشفة، confidence، STT data...
            $table->json('analysis')->nullable();

            // معلومات إضافية خاصة بالـAI / الصوت / provider
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(
                ['speaking_session_id', 'turn_number'],
                'speaking_session_turn_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speaking_turns');
    }
};

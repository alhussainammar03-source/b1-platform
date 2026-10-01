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
        Schema::create('writing_evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('writing_submission_id')
                ->constrained()
                ->cascadeOnDelete();

            // لغة الشرح الإضافية للمستخدم: de, ar, en, tr, uk
            $table->string('feedback_language', 5)->default('de');

            // pending | evaluating | evaluated | failed
            $table->string('status', 30)->default('pending');

            // تقييم منظم حسب المعايير
            $table->json('criteria')->nullable();

            // النسخة الألمانية المصححة
            $table->longText('corrected_text')->nullable();

            // مثال محسّن مناسب لمستوى B1
            $table->longText('improved_example')->nullable();

            // ملاحظات عامة بالألمانية
            $table->longText('feedback_de')->nullable();

            // نفس الشرح بلغة المستخدم
            $table->longText('feedback_translated')->nullable();

            // الأخطاء المكتشفة مع التصحيح والشرح
            $table->json('errors')->nullable();

            // نقاط المهمة التي لم تتم معالجتها
            $table->json('missing_required_points')->nullable();

            // Darauf solltest du achten
            $table->json('focus_points')->nullable();

            // معلومات تقنية عن عملية AI
            $table->string('provider', 50)->nullable();
            $table->string('model', 100)->nullable();

            $table->json('meta')->nullable();

            $table->timestamp('evaluated_at')->nullable();

            $table->timestamps();

            $table->index(['writing_submission_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('writing_evaluations');
    }
};

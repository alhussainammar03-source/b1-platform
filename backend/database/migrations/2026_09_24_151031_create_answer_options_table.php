<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answer_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();

            // نص الخيار بالألمانية
            $table->text('text');

            // هل هذا الخيار صحيح؟
            $table->boolean('is_correct')->default(false);

            // شرح يظهر بعد الإجابة عند الحاجة
            $table->text('explanation')->nullable();

            // بيانات إضافية لأنواع أسئلة خاصة
            $table->json('meta')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'question_id',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answer_options');
    }
};

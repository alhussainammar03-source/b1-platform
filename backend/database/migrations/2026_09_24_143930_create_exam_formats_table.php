<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_formats', function (Blueprint $table) {
            $table->id();

            // مثال: dtz, telc_b1, goethe_b1
            $table->string('key', 50)->unique();

            // اسم الامتحان بعدة لغات
            $table->json('name');

            // وصف اختياري بعدة لغات
            $table->json('description')->nullable();

            // مثال: B1 أو A2-B1
            $table->string('level', 20)->default('B1');

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_formats');
    }
};

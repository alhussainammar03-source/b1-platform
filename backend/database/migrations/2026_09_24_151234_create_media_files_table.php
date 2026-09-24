<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();

            // image, audio, document ...
            $table->string('type', 50);

            // Laravel filesystem disk: public, s3 ...
            $table->string('disk', 50)->default('public');

            // Path داخل الـ storage
            $table->string('path');

            $table->string('original_name')->nullable();

            $table->string('mime_type', 100)->nullable();

            // Bytes
            $table->unsignedBigInteger('size')->nullable();

            // للصور
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // للصوت بالثواني
            $table->unsignedInteger('duration_seconds')->nullable();

            // Accessibility
            $table->json('alt_text')->nullable();

            // معلومات المصدر والترخيص
            $table->string('source')->nullable();
            $table->string('license')->nullable();
            $table->text('attribution')->nullable();

            // أحجام/نسخ مولدة لاحقاً
            $table->json('variants')->nullable();

            // معلومات إضافية عند الحاجة
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};

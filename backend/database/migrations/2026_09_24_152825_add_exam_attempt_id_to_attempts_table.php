<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->foreignId('exam_attempt_id')
                ->nullable()
                ->after('user_id')
                ->constrained('exam_attempts')
                ->cascadeOnDelete();

            $table->index([
                'exam_attempt_id',
                'exercise_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropIndex([
                'exam_attempt_id',
                'exercise_id',
            ]);

            $table->dropConstrainedForeignId('exam_attempt_id');
        });
    }
};

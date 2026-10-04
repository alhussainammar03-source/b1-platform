<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table
                ->foreignId('exercise_stimulus_id')
                ->nullable()
                ->after('exercise_id')
                ->constrained('exercise_stimuli')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'exercise_stimulus_id'
            );
        });
    }
};

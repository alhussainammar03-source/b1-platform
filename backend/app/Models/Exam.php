<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Exam extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'description',
    ];

    protected $fillable = [
        'exam_format_id',
        'key',
        'title',
        'description',
        'mode',
        'access_level',
        'status',
        'duration_minutes',
        'sort_order',
        'published_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function examFormat(): BelongsTo
    {
        return $this->belongsTo(ExamFormat::class);
    }

    public function examExercises(): HasMany
    {
        return $this->hasMany(ExamExercise::class)
            ->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}

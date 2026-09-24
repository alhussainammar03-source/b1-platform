<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'description',
    ];

    protected $fillable = [
        'exam_format_id',
        'exam_section_id',
        'exam_part_id',
        'key',
        'type',
        'title',
        'description',
        'instructions',
        'difficulty',
        'access_level',
        'status',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function examFormat(): BelongsTo
    {
        return $this->belongsTo(ExamFormat::class);
    }

    public function examSection(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class);
    }

    public function examPart(): BelongsTo
    {
        return $this->belongsTo(ExamPart::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
    public function stimuli(): HasMany
    {
        return $this->hasMany(ExerciseStimulus::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExerciseItem::class);
    }
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpeakingSession extends Model
{
    protected $fillable = [
        'user_id',
        'exam_format_id',
        'exercise_id',
        'exam_part_id',
        'mode',
        'status',
        'current_part',
        'covered_topics',
        'meta',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'covered_topics' => 'array',
            'meta' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function examFormat(): BelongsTo
    {
        return $this->belongsTo(ExamFormat::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function examPart(): BelongsTo
    {
        return $this->belongsTo(ExamPart::class);
    }

    public function turns(): HasMany
    {
        return $this->hasMany(SpeakingTurn::class)
            ->orderBy('turn_number');
    }

    public function latestUserTurn(): ?SpeakingTurn
    {
        return $this->turns()
            ->where('speaker', 'user')
            ->reorder('turn_number', 'desc')
            ->first();
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeakingTurn extends Model
{
    protected $fillable = [
        'speaking_session_id',
        'exam_part_id',
        'question_id',
        'speaker',
        'text',
        'audio_path',
        'audio_duration_ms',
        'turn_number',
        'turn_type',
        'analysis',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'analysis' => 'array',
            'meta' => 'array',
            'audio_duration_ms' => 'integer',
            'turn_number' => 'integer',
        ];
    }

    public function speakingSession(): BelongsTo
    {
        return $this->belongsTo(SpeakingSession::class);
    }

    public function examPart(): BelongsTo
    {
        return $this->belongsTo(ExamPart::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

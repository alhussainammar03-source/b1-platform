<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WritingEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'writing_submission_id',
        'feedback_language',
        'status',
        'criteria',
        'corrected_text',
        'improved_example',
        'feedback_de',
        'feedback_translated',
        'errors',
        'missing_required_points',
        'focus_points',
        'provider',
        'model',
        'meta',
        'evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'errors' => 'array',
            'missing_required_points' => 'array',
            'focus_points' => 'array',
            'meta' => 'array',
            'evaluated_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(
            WritingSubmission::class,
            'writing_submission_id'
        );
    }
}

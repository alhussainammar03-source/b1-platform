<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class WritingSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'exercise_id',
        'question_id',
        'input_method',
        'original_text',
        'image_disk',
        'image_path',
        'extracted_text',
        'confirmed_text',
        'status',
        'confirmed_at',
        'evaluated_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'confirmed_at' => 'datetime',
            'evaluated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }


    public function evaluations(): HasMany
    {
        return $this->hasMany(WritingEvaluation::class);
    }
}

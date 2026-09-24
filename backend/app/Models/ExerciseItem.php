<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'question_id',
        'kind',
        'text',
        'translation',
        'meta',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'translation' => 'array',
            'meta' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ExamPart extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'label',
    ];

    protected $fillable = [
        'exam_format_id',
        'exam_section_id',
        'key',
        'task_kind',
        'title',
        'label',
        'default_prep_seconds',
        'default_speak_seconds',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_prep_seconds' => 'integer',
            'default_speak_seconds' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
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
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }
    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamFormat extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'key',
        'name',
        'description',
        'level',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }


    public function parts(): HasMany
    {
        return $this->hasMany(ExamPart::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}

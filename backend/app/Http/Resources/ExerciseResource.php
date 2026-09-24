<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'type' => $this->type,

            'title' => $this->title,
            'description' => $this->description,
            'instructions' => $this->instructions,

            'difficulty' => $this->difficulty,
            'access_level' => $this->access_level,

            'stimuli' => $this->stimuli
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->values()
                ->map(function ($stimulus) {
                    return [
                        'id' => $stimulus->id,
                        'type' => $stimulus->type,
                        'label' => $stimulus->label,
                        'content' => $stimulus->content,
                        'media_file_id' => $stimulus->media_file_id,
                        'meta' => $stimulus->meta,
                    ];
                }),

            'questions' => $this->questions
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->values()
                ->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'type' => $question->type,
                        'prompt' => $question->prompt,
                        'instructions' => $question->instructions,
                        'points' => $question->points,

                        'options' => $question->answerOptions
                            ->where('is_active', true)
                            ->sortBy('sort_order')
                            ->values()
                            ->map(function ($option) {
                                return [
                                    'id' => $option->id,
                                    'text' => $option->text,
                                ];
                            }),
                    ];
                }),
        ];
    }
}

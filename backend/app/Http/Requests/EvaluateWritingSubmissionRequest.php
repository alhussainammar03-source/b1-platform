<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvaluateWritingSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'feedback_language' => [
                'nullable',
                'string',
                Rule::in(['de', 'ar', 'en', 'tr', 'uk']),
            ],
        ];
    }
}

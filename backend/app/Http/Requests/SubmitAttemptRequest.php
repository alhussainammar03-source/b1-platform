<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => [
                'required',
                'array',
                'min:1',
            ],

            'answers.*.question_id' => [
                'required',
                'integer',
                'distinct',
                'exists:questions,id',
            ],

            'answers.*.answer_option_id' => [
                'nullable',
                'integer',
                'exists:answer_options,id',
            ],

            'answers.*.text_answer' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'answers.*.media_file_id' => [
                'nullable',
                'integer',
                'exists:media_files,id',
            ],
        ];
    }
}

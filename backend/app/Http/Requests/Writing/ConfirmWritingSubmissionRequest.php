<?php

namespace App\Http\Requests\Writing;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmWritingSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirmed_text' => [
                'required',
                'string',
                'max:10000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmed_text.required' => 'Bitte bestätigen oder korrigieren Sie den erkannten Text.',
            'confirmed_text.string' => 'Der bestätigte Text muss ein gültiger Text sein.',
            'confirmed_text.max' => 'Der Text darf maximal 10.000 Zeichen enthalten.',
        ];
    }
}

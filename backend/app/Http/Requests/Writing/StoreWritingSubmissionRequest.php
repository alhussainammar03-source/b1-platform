<?php

namespace App\Http\Requests\Writing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWritingSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => [
                'required',
                'integer',
                'exists:questions,id',
            ],

            'input_method' => [
                'required',
                Rule::in([
                    'text',
                    'handwritten_image',
                ]),
            ],

            'text' => [
                'nullable',
                'string',
                'max:10000',
                'required_if:input_method,text',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
                'required_if:input_method,handwritten_image',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'question_id.required' => 'Die Schreibaufgabe fehlt.',
            'question_id.exists' => 'Die Schreibaufgabe wurde nicht gefunden.',

            'input_method.required' => 'Bitte wählen Sie eine Eingabemethode.',
            'input_method.in' => 'Die Eingabemethode ist ungültig.',

            'text.required_if' => 'Bitte schreiben Sie einen Text.',

            'image.required_if' => 'Bitte laden Sie ein Foto Ihrer handschriftlichen Antwort hoch.',
            'image.image' => 'Die hochgeladene Datei muss ein Bild sein.',
            'image.mimes' => 'Erlaubt sind JPG, JPEG, PNG und WebP.',
            'image.max' => 'Das Bild darf maximal 10 MB groß sein.',
        ];
    }
}

<?php

namespace App\Services\Writing;

use App\Models\WritingSubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OpenAIHandwritingExtractionService implements HandwritingExtractionService
{
    public function extract(WritingSubmission $submission): string
    {
        if (! $submission->image_disk || ! $submission->image_path) {
            throw new RuntimeException('Die Schreibantwort enthält kein Bild.');
        }

        $disk = Storage::disk($submission->image_disk);

        if (! $disk->exists($submission->image_path)) {
            throw new RuntimeException('Das Bild wurde nicht gefunden.');
        }

        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model');

        if (! $apiKey) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $imageContents = $disk->get($submission->image_path);

        $mimeType = $disk->mimeType($submission->image_path)
            ?: 'image/jpeg';

        $base64Image = base64_encode($imageContents);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(60)
            ->retry(2, 1000)
            ->post('https://api.openai.com/v1/responses', [
                'model' => $model,

                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => <<<'PROMPT'
Transcribe the handwritten German text in this image.

Rules:
- Return only the text visible in the image.
- Preserve paragraphs and line breaks where reasonable.
- Preserve spelling and grammar exactly as written.
- Do not correct mistakes.
- Do not improve the text.
- Do not explain anything.
- If a word cannot be read confidently, write [unleserlich].
PROMPT,
                            ],
                            [
                                'type' => 'input_image',
                                'image_url' => sprintf(
                                    'data:%s;base64,%s',
                                    $mimeType,
                                    $base64Image
                                ),
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'OpenAI handwriting extraction failed: ' .
                    $response->body()
            );
        }

        $text = data_get($response->json(), 'output.0.content.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException(
                'OpenAI returned no handwritten text.'
            );
        }

        return trim($text);
    }
}

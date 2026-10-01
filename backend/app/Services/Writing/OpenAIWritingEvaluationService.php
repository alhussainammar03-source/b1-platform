<?php

namespace App\Services\Writing;

use App\Models\WritingEvaluation;
use App\Models\WritingSubmission;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIWritingEvaluationService implements WritingEvaluationService
{
    public function evaluate(
        WritingSubmission $submission,
        WritingEvaluation $evaluation
    ): array {
        if (
            $submission->status !== 'ready_for_evaluation' ||
            ! $submission->confirmed_text
        ) {
            throw new RuntimeException(
                'Die Schreibantwort ist noch nicht bereit für die Bewertung.'
            );
        }

        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model');

        if (! $apiKey) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $submission->loadMissing('question');

        $question = $submission->question;

        if (! $question) {
            throw new RuntimeException(
                'Die Schreibaufgabe wurde nicht gefunden.'
            );
        }

        $feedbackLanguage = $this->normalizeLanguage(
            $evaluation->feedback_language
        );

        $languageName = $this->languageName($feedbackLanguage);

        $taskText = $this->extractQuestionText($question);

        $prompt = <<<PROMPT
You are a German B1 writing tutor for exam preparation.

Evaluate the learner's German writing as TRAINING FEEDBACK.
This is not an official DTZ, Goethe or telc examination result.

TASK:
{$taskText}

LEARNER TEXT:
{$submission->confirmed_text}

ADDITIONAL FEEDBACK LANGUAGE:
{$languageName}

Important rules:

1. Evaluate the learner's actual text. Do not invent errors.
2. Consider whether the learner answered the requirements of the task.
3. The corrected_text must remain close to the learner's original text.
4. The improved_example should be a natural German B1-level example.
5. Do not make the German unnecessarily advanced.
6. German explanations must be written in clear, learner-friendly German.
7. feedback_translated must be written in {$languageName}.
8. If the additional language is German, feedback_translated may contain the same German explanation.
9. German example sentences and corrections must remain in German.
10. Preserve the meaning the learner intended wherever possible.
11. Do not claim that the learner officially passed or failed an exam.
12. Do not provide an official examination score.
13. Return JSON only. Do not use Markdown.

Return exactly this structure:

{
  "criteria": {
    "task_completion": {
      "feedback_de": "string",
      "feedback_translated": "string"
    },
    "grammar": {
      "feedback_de": "string",
      "feedback_translated": "string"
    },
    "spelling": {
      "feedback_de": "string",
      "feedback_translated": "string"
    },
    "vocabulary": {
      "feedback_de": "string",
      "feedback_translated": "string"
    },
    "organization": {
      "feedback_de": "string",
      "feedback_translated": "string"
    }
  },
  "corrected_text": "string",
  "improved_example": "string",
  "feedback_de": "string",
  "feedback_translated": "string",
  "errors": [
    {
      "original": "string",
      "correction": "string",
      "category": "grammar|spelling|vocabulary|word_order|punctuation|register|other",
      "explanation_de": "string",
      "explanation_translated": "string"
    }
  ],
  "missing_required_points": [
    {
      "point_de": "string",
      "point_translated": "string"
    }
  ],
  "focus_points": [
    {
      "text_de": "string",
      "text_translated": "string"
    }
  ]
}

If there are no errors or no missing task points, return an empty array for that field.
PROMPT;

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(90)
            ->retry(2, 1000)
            ->post('https://api.openai.com/v1/responses', [
                'model' => $model,
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => $prompt,
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'OpenAI writing evaluation failed: ' .
                    $response->body()
            );
        }

        $outputText = data_get(
            $response->json(),
            'output.0.content.0.text'
        );

        if (! is_string($outputText) || trim($outputText) === '') {
            throw new RuntimeException(
                'OpenAI returned no writing evaluation.'
            );
        }

        $result = json_decode($outputText, true);

        if (! is_array($result)) {
            throw new RuntimeException(
                'OpenAI returned an invalid writing evaluation.'
            );
        }

        $this->validateResult($result);

        return $result;
    }

    private function normalizeLanguage(?string $language): string
    {
        $allowed = ['de', 'ar', 'en', 'tr', 'uk'];

        return in_array($language, $allowed, true)
            ? $language
            : 'de';
    }

    private function languageName(string $language): string
    {
        return match ($language) {
            'ar' => 'Arabic',
            'en' => 'English',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            default => 'German',
        };
    }




    private function extractQuestionText($question): string
    {
        $parts = [];

        if ($question->prompt) {
            $parts[] = 'Aufgabe: ' . $question->prompt;
        }

        if ($question->instructions) {
            $parts[] = 'Anweisung: ' . $question->instructions;
        }

        $requiredPoints = $question->meta['required_points'] ?? [];

        if (is_array($requiredPoints) && count($requiredPoints) > 0) {
            $parts[] = "Zu bearbeitende Punkte:\n- " .
                implode("\n- ", $requiredPoints);
        }

        return implode("\n\n", $parts);
    }

    private function validateResult(array $result): void
    {
        $required = [
            'criteria',
            'corrected_text',
            'improved_example',
            'feedback_de',
            'feedback_translated',
            'errors',
            'missing_required_points',
            'focus_points',
        ];

        foreach ($required as $key) {
            if (! array_key_exists($key, $result)) {
                throw new RuntimeException(
                    "OpenAI evaluation is missing field: {$key}"
                );
            }
        }

        $criteria = [
            'task_completion',
            'grammar',
            'spelling',
            'vocabulary',
            'organization',
        ];

        foreach ($criteria as $criterion) {
            if (! isset($result['criteria'][$criterion])) {
                throw new RuntimeException(
                    "OpenAI evaluation is missing criterion: {$criterion}"
                );
            }
        }
    }
}

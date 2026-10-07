<?php

namespace App\Services\Speaking\Providers;

use App\Models\SpeakingSession;
use App\Services\Speaking\Contracts\SpeakingAiProvider;
use Illuminate\Support\Facades\Http;

class OpenAiSpeakingProvider implements SpeakingAiProvider
{
    public function analyzePartOneResponse(
        SpeakingSession $session,
        string $text
    ): array {
        $conversation = $this->buildConversationContext($session);

        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.speaking_model');
        if (empty($apiKey) || empty($model)) {
            throw new \RuntimeException(
                'OpenAI speaking configuration is missing.'
            );
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(30)
            ->post('https://api.openai.com/v1/responses', [
                'model' => $model,

                'instructions' => $this->partOneSystemPrompt(),

                'input' => [
                    [
                        'role' => 'user',
                        'content' => json_encode([
                            'conversation_history' => $conversation,
                            'already_covered_topics' => $session->covered_topics ?? [],
                            'latest_participant_answer' => $text,
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ],

                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'b1_speaking_part_one_analysis',
                        'strict' => true,
                        'schema' => $this->partOneResponseSchema(),
                    ],
                ],
            ]);

        $response->throw();

        $data = $response->json();

        $outputText = data_get(
            $data,
            'output.0.content.0.text'
        );

        if (! is_string($outputText) || $outputText === '') {
            throw new \RuntimeException(
                'OpenAI returned no speaking analysis.'
            );
        }

        $analysis = json_decode(
            $outputText,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $analysis;
    }

    private function buildConversationContext(
        SpeakingSession $session
    ): array {
        return $session->turns
            ->sortBy('turn_number')
            ->map(fn($turn) => [
                'speaker' => $turn->speaker,
                'text' => $turn->text,
                'turn_type' => $turn->turn_type,
            ])
            ->values()
            ->all();
    }


    private function partOneResponseSchema(): array
    {
        return [
            'type' => 'object',

            'properties' => [
                'covered_topics' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => [
                            'name',
                            'age',
                            'origin',
                            'residence',
                            'profession',
                            'hobbies',
                        ],
                    ],
                ],

                'extracted_information' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'type' => ['string', 'null'],
                        ],
                        'age' => [
                            'type' => ['integer', 'null'],
                        ],
                        'origin' => [
                            'type' => ['string', 'null'],
                        ],
                        'residence' => [
                            'type' => ['string', 'null'],
                        ],
                        'profession' => [
                            'type' => ['string', 'null'],
                        ],
                        'hobbies' => [
                            'type' => ['string', 'null'],
                        ],
                    ],
                    'required' => [
                        'name',
                        'age',
                        'origin',
                        'residence',
                        'profession',
                        'hobbies',
                    ],
                    'additionalProperties' => false,
                ],

                'suggested_follow_up' => [
                    'type' => ['string', 'null'],
                ],
            ],

            'required' => [
                'covered_topics',
                'extracted_information',
                'suggested_follow_up',
            ],

            'additionalProperties' => false,
        ];
    }

    private function partOneSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an AI conversation analyst for German B1 speaking-exam training.

You are analyzing Part 1 (Vorstellung).

The participant is expected to provide information about these six topics:

- name: the participant's name
- age: the participant's age
- origin: where the participant comes from
- residence: where the participant currently lives
- profession: job, work, studies, training, or current occupation
- hobbies: hobbies and free-time activities

Your task is to analyze ONLY the participant's latest answer while using the conversation history for context.

Rules:

1. Determine which of the six topics are actually answered in the participant's latest answer.

2. Do not mark a topic as covered just because the examiner asked about it.

3. Do not invent or assume information that the participant did not provide.

4. Understand meaning, not only exact keywords.
For example:
"Ich komme ursprünglich aus Syrien."
covers origin.

"Seit drei Jahren wohne ich in Essen."
covers residence.

"Ich arbeite als Softwareentwickler."
covers profession.

"In meiner Freizeit spiele ich Fußball und gehe ins Fitnessstudio."
covers hobbies.

5. Use the conversation history to understand short contextual answers.
For example:

Examiner:
"Wie alt sind Sie?"

Participant:
"30."

This covers age.

Examiner:
"Was machen Sie beruflich?"

Participant:
"Softwareentwickler."

This covers profession.

6. extracted_information must contain only information supported by the participant's answer and its conversational context.
Use null when information is not available.

7. suggested_follow_up must be a short, natural German B1-level examiner question.

8. Prefer asking about an important required topic that has not yet been covered.

9. If all required topics are already covered, suggested_follow_up may be a natural personal follow-up based on something the participant mentioned.

Example:
Participant says:
"In meiner Freizeit spiele ich gern Fußball."

A suitable follow-up could be:
"Wie oft spielen Sie Fußball?"

10. Do not evaluate grammar, pronunciation, vocabulary, or exam score in this task.

11. Do not correct the participant's German.

12. Keep the examiner question friendly, natural, concise, and appropriate for B1 level.

Return only data matching the required structured output schema.
PROMPT;
    }
}

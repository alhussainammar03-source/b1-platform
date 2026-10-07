<?php

namespace Tests\Unit\Speaking;

use App\Models\SpeakingSession;
use App\Services\Speaking\Providers\OpenAiSpeakingProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiSpeakingProviderTest extends TestCase
{
    public function test_it_analyzes_part_one_response_using_structured_openai_output(): void
    {
        config([
            'services.openai.api_key' => 'test-key',
            'services.openai.speaking_model' => 'test-model',
        ]);

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output' => [
                    [
                        'content' => [
                            [
                                'type' => 'output_text',
                                'text' => json_encode([
                                    'covered_topics' => [
                                        'name',
                                        'age',
                                        'origin',
                                        'residence',
                                    ],
                                    'extracted_information' => [
                                        'name' => 'Ahmad',
                                        'age' => 30,
                                        'origin' => 'Syrien',
                                        'residence' => 'Essen',
                                        'profession' => null,
                                        'hobbies' => null,
                                    ],
                                    'suggested_follow_up' => 'Was machen Sie beruflich?',
                                ], JSON_UNESCAPED_UNICODE),
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $session = new SpeakingSession([
            'covered_topics' => [
                'name' => false,
                'age' => false,
                'origin' => false,
                'residence' => false,
                'profession' => false,
                'hobbies' => false,
            ],
        ]);

        $session->setRelation('turns', collect());

        $provider = new OpenAiSpeakingProvider();

        $analysis = $provider->analyzePartOneResponse(
            $session,
            'Ich heiße Ahmad, bin 30 Jahre alt, komme aus Syrien und wohne in Essen.'
        );

        $this->assertSame(
            ['name', 'age', 'origin', 'residence'],
            $analysis['covered_topics']
        );

        $this->assertSame(
            'Ahmad',
            $analysis['extracted_information']['name']
        );

        $this->assertSame(
            30,
            $analysis['extracted_information']['age']
        );

        $this->assertSame(
            'Syrien',
            $analysis['extracted_information']['origin']
        );

        $this->assertSame(
            'Essen',
            $analysis['extracted_information']['residence']
        );

        $this->assertNull(
            $analysis['extracted_information']['profession']
        );

        $this->assertNull(
            $analysis['extracted_information']['hobbies']
        );

        $this->assertSame(
            'Was machen Sie beruflich?',
            $analysis['suggested_follow_up']
        );

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.openai.com/v1/responses'
                && $request['model'] === 'test-model'
                && $request['text']['format']['type'] === 'json_schema'
                && $request['text']['format']['strict'] === true
                && $request['text']['format']['name']
                === 'b1_speaking_part_one_analysis';
        });
    }
}

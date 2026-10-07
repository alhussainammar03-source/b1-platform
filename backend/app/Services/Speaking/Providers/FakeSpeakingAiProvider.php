<?php

namespace App\Services\Speaking\Providers;

use App\Models\SpeakingSession;
use App\Services\Speaking\Contracts\SpeakingAiProvider;

class FakeSpeakingAiProvider implements SpeakingAiProvider
{
    public function __construct(
        private array $partOneAnalysis = []
    ) {}

    public function setPartOneAnalysis(array $analysis): void
    {
        $this->partOneAnalysis = $analysis;
    }

    public function analyzePartOneResponse(
        SpeakingSession $session,
        string $text
    ): array {
        if (! empty($this->partOneAnalysis)) {
            return $this->partOneAnalysis;
        }

        return [
            'covered_topics' => [],
            'extracted_information' => [],
            'suggested_follow_up' => null,
        ];
    }
}

<?php

namespace App\Services\Speaking;

use App\Models\SpeakingSession;
use App\Services\Speaking\Contracts\SpeakingAiProvider;

class SpeakingResponseAnalysisService
{


    public const PART_ONE_TOPICS = [
        'name',
        'age',
        'origin',
        'residence',
        'profession',
        'hobbies',
    ];

    public function __construct(
        private readonly SpeakingAiProvider $aiProvider
    ) {}


    public function emptyPartOneAnalysis(): array
    {
        return [
            'covered_topics' => [],
            'missing_topics' => self::PART_ONE_TOPICS,
            'extracted_information' => [],
            'suggested_follow_up' => null,
            'all_required_topics_covered' => false,
        ];
    }

    public function normalizePartOneAnalysis(array $analysis): array
    {
        $coveredTopics = array_values(array_intersect(
            self::PART_ONE_TOPICS,
            $analysis['covered_topics'] ?? []
        ));

        $missingTopics = array_values(array_diff(
            self::PART_ONE_TOPICS,
            $coveredTopics
        ));

        return [
            'covered_topics' => $coveredTopics,

            'missing_topics' => $missingTopics,

            'extracted_information' =>
            $analysis['extracted_information'] ?? [],

            'suggested_follow_up' =>
            $analysis['suggested_follow_up'] ?? null,

            'all_required_topics_covered' =>
            count($missingTopics) === 0,
        ];
    }


    public function analyzePartOneText(
        SpeakingSession $session,
        string $text
    ): array {
        $analysis = $this->aiProvider->analyzePartOneResponse(
            $session,
            $text
        );

        return $this->normalizePartOneAnalysis($analysis);
    }
}

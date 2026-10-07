<?php

namespace App\Services\Speaking\Contracts;

use App\Models\SpeakingSession;

interface SpeakingAiProvider
{
    /**
     * Analyze a participant's answer in Teil 1.
     *
     * Expected result:
     *
     * [
     *     'covered_topics' => [],
     *     'extracted_information' => [],
     *     'suggested_follow_up' => null,
     * ]
     */
    public function analyzePartOneResponse(
        SpeakingSession $session,
        string $text
    ): array;
}

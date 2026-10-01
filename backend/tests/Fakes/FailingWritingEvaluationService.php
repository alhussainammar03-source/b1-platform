<?php

namespace Tests\Fakes;

use App\Models\WritingEvaluation;
use App\Models\WritingSubmission;
use App\Services\Writing\WritingEvaluationService;
use RuntimeException;

class FailingWritingEvaluationService implements WritingEvaluationService
{
    public function evaluate(
        WritingSubmission $submission,
        WritingEvaluation $evaluation
    ): array {
        throw new RuntimeException('Fake AI evaluation failure.');
    }
}

<?php

namespace App\Services\Writing;

use App\Models\WritingEvaluation;
use App\Models\WritingSubmission;

interface WritingEvaluationService
{
    public function evaluate(
        WritingSubmission $submission,
        WritingEvaluation $evaluation
    ): array;
}

<?php

namespace App\Jobs;

use App\Models\WritingEvaluation;
use App\Services\Writing\WritingEvaluationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class EvaluateWritingJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $evaluationId
    ) {}

    public function handle(
        WritingEvaluationService $evaluationService
    ): void {
        $evaluation = WritingEvaluation::with([
            'submission.question',
        ])->findOrFail($this->evaluationId);

        $submission = $evaluation->submission;

        try {
            $evaluation->update([
                'status' => 'evaluating',
            ]);

            $result = $evaluationService->evaluate(
                $submission,
                $evaluation
            );

            $evaluation->update([
                'criteria' => $result['criteria'],
                'corrected_text' => $result['corrected_text'],
                'improved_example' => $result['improved_example'],
                'feedback_de' => $result['feedback_de'],
                'feedback_translated' => $result['feedback_translated'],
                'errors' => $result['errors'],
                'missing_required_points' => $result['missing_required_points'],
                'focus_points' => $result['focus_points'],

                'provider' => 'openai',
                'model' => config('services.openai.model'),

                'status' => 'evaluated',
                'evaluated_at' => now(),
            ]);

            $submission->update([
                'status' => 'evaluated',
                'evaluated_at' => now(),
            ]);
        }  catch (Throwable $exception) {
    $evaluation->update([
        'status' => 'evaluating',
        'meta' => [
            'error' => $exception->getMessage(),
        ],
    ]);

    throw $exception;
}
    }



    public function failed(Throwable $exception): void
    {
        $evaluation = WritingEvaluation::with('submission')
            ->find($this->evaluationId);

        if (! $evaluation) {
            return;
        }

        $evaluation->update([
            'status' => 'failed',
            'meta' => [
                'error' => $exception->getMessage(),
            ],
        ]);

        if ($evaluation->submission) {
            $evaluation->submission->update([
                'status' => 'failed',
            ]);
        }
    }

}

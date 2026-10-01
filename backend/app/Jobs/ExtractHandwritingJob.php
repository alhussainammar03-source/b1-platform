<?php

namespace App\Jobs;

use App\Models\WritingSubmission;
use App\Services\Writing\HandwritingExtractionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ExtractHandwritingJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $submissionId
    ) {}

    public function handle(
        HandwritingExtractionService $extractionService
    ): void {
        $submission = WritingSubmission::findOrFail($this->submissionId);

        try {
            $submission->update([
                'status' => 'extracting',
            ]);

            $extractedText = $extractionService->extract($submission);

            $submission->update([
                'extracted_text' => $extractedText,
                'status' => 'awaiting_confirmation',
            ]);
        } catch (Throwable $exception) {
            $submission->update([
                'status' => 'failed',
            ]);

            throw $exception;
        }
    }
}

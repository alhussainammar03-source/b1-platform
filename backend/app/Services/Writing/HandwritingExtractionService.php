<?php

namespace App\Services\Writing;

use App\Models\WritingSubmission;

interface HandwritingExtractionService
{
    public function extract(WritingSubmission $submission): string;
}

<?php

namespace App\Services\Writing;

use App\Models\WritingSubmission;

class FakeHandwritingExtractionService implements HandwritingExtractionService
{
    public function extract(WritingSubmission $submission): string
    {
        return <<<'TEXT'
Sehr geehrte Frau Berger,

leider kann ich am Montag und Dienstag nicht zum Deutschkurs kommen, weil ich krank bin.

Können Sie mir bitte sagen, welche Hausaufgaben wir haben?
Bitte schicken Sie mir auch die Unterrichtsmaterialien.

Vielen Dank.

Mit freundlichen Grüßen
Max Mustermann
TEXT;
    }
}

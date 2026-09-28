<?php

namespace App\Enums;

enum QuestionType: string
{
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TRUE_FALSE = 'true_false';
    case MATCHING = 'matching';
    case GAP_FILL = 'gap_fill';
    case WRITING_TEXT = 'writing_text';
    case SPEAKING_RESPONSE = 'speaking_response';

    public function isAutoGradable(): bool
    {
        return match ($this) {
            self::MULTIPLE_CHOICE,
            self::TRUE_FALSE,
            self::MATCHING,
            self::GAP_FILL => true,

            self::WRITING_TEXT,
            self::SPEAKING_RESPONSE => false,
        };
    }
}

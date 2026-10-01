<?php

namespace Tests\Fakes;

use App\Models\WritingEvaluation;
use App\Models\WritingSubmission;
use App\Services\Writing\WritingEvaluationService;

class FakeWritingEvaluationService implements WritingEvaluationService
{
    public function evaluate(
        WritingSubmission $submission,
        WritingEvaluation $evaluation
    ): array {
        return [
            'criteria' => [
                'task_completion' => [
                    'feedback_de' => 'Die Aufgabe wurde erfüllt.',
                    'feedback_translated' => 'تم تنفيذ المهمة.',
                ],
                'grammar' => [
                    'feedback_de' => 'Die Grammatik ist überwiegend korrekt.',
                    'feedback_translated' => 'القواعد صحيحة إلى حد كبير.',
                ],
                'spelling' => [
                    'feedback_de' => 'Achte auf die Rechtschreibung.',
                    'feedback_translated' => 'انتبه إلى الإملاء.',
                ],
                'vocabulary' => [
                    'feedback_de' => 'Der Wortschatz passt zum Niveau B1.',
                    'feedback_translated' => 'المفردات مناسبة لمستوى B1.',
                ],
                'organization' => [
                    'feedback_de' => 'Der Text ist gut aufgebaut.',
                    'feedback_translated' => 'النص منظم بشكل جيد.',
                ],
            ],

            'corrected_text' =>
            'Sehr geehrte Frau Berger, ich kann leider nicht kommen.',

            'improved_example' =>
            'Sehr geehrte Frau Berger, leider kann ich am Montag nicht zum Kurs kommen.',

            'feedback_de' =>
            'Der Text ist verständlich. Achte besonders auf die Satzstellung.',

            'feedback_translated' =>
            'النص مفهوم. انتبه بشكل خاص إلى ترتيب الكلمات.',

            'errors' => [
                [
                    'original' => 'weil ich bin krank',
                    'correction' => 'weil ich krank bin',
                    'category' => 'word_order',
                    'explanation_de' =>
                    'Nach „weil“ steht das Verb am Ende.',
                    'explanation_translated' =>
                    'بعد weil يأتي الفعل في نهاية الجملة.',
                ],
            ],

            'missing_required_points' => [],

            'focus_points' => [
                [
                    'text_de' =>
                    'Achte auf die Verbstellung in Nebensätzen.',
                    'text_translated' =>
                    'انتبه إلى موقع الفعل في الجمل الفرعية.',
                ],
            ],
        ];
    }
}

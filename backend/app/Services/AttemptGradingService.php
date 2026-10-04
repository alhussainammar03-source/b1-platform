<?php

namespace App\Services;

use App\Models\AnswerOption;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Enums\QuestionType;

class AttemptGradingService
{
    public function grade(Attempt $attempt, array $answers): array
    {
        $exercise = $attempt->exercise()
            ->with('questions.answerOptions')
            ->firstOrFail();

        $questions = $exercise->questions
            ->where('is_active', true)
            ->keyBy('id');
        $hasManualGrading = $questions->contains(
            fn($question) => ! $question->type->isAutoGradable()
        );

        if ($hasManualGrading) {
            throw new HttpException(
                422,
                'This exercise contains questions that require manual or AI grading.'
            );
        }


        $submittedAnswers = collect($answers);

        $this->validateQuestions($questions, $submittedAnswers);

        return DB::transaction(function () use (
            $attempt,
            $questions,
            $submittedAnswers
        ) {
            $score = 0.0;
            $answerResults = [];
            $maxScore = (float) $questions->sum(
                fn($question) => (float) $question->points
            );

            foreach ($submittedAnswers as $answer) {
                $question = $questions->get($answer['question_id']);

                $answerOptionId = $answer['answer_option_id'] ?? null;

                $isCorrect = null;
                $awardedPoints = null;

                if ($answerOptionId !== null) {
                    $option = AnswerOption::query()
                        ->where('id', $answerOptionId)
                        ->where('question_id', $question->id)
                        ->where('is_active', true)
                        ->first();

                    if (! $option) {
                        throw new HttpException(
                            422,
                            'Invalid answer option for this question.'
                        );
                    }

                    $isCorrect = (bool) $option->is_correct;

                    $awardedPoints = $isCorrect
                        ? (float) $question->points
                        : 0.0;

                    $score += $awardedPoints;
                }

                AttemptAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'answer_option_id' => $answerOptionId,
                        'text_answer' => $answer['text_answer'] ?? null,
                        'media_file_id' => $answer['media_file_id'] ?? null,
                        'awarded_points' => $awardedPoints,
                        'is_correct' => $isCorrect,
                        'answered_at' => now(),
                    ]
                );


                $correctOption = $question->answerOptions
                    ->first(fn($option) => $option->is_active && $option->is_correct);

                $answerResults[] = [
                    'question_id' => $question->id,
                    'selected_option_id' => $answerOptionId,
                    'is_correct' => $isCorrect,
                    'awarded_points' => $awardedPoints,
                    'max_points' => (float) $question->points,
                    'correct_option' => $correctOption
                        ? [
                            'id' => $correctOption->id,
                            'text' => $correctOption->text,
                        ]
                        : null,
                ];
            }

            $percentage = $maxScore > 0
                ? round(($score / $maxScore) * 100, 2)
                : 0;

            $attempt->update([
                'status' => 'graded',
                'score' => $score,
                'max_score' => $maxScore,
                'percentage' => $percentage,
                'submitted_at' => now(),
                'graded_at' => now(),
            ]);

            return [
                'attempt_id' => $attempt->id,
                'status' => $attempt->status,
                'score' => $attempt->score,
                'max_score' => $attempt->max_score,
                'percentage' => $attempt->percentage,
                'answers' => $answerResults,
            ];
        });
    }

    private function validateQuestions(
        Collection $questions,
        Collection $submittedAnswers
    ): void {
        foreach ($submittedAnswers as $answer) {
            if (! $questions->has($answer['question_id'])) {
                throw new HttpException(
                    422,
                    'Invalid question for this attempt.'
                );
            }
        }
    }
}

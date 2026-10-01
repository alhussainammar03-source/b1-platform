<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Writing\StoreWritingSubmissionRequest;
use App\Models\Question;
use App\Models\WritingSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Writing\ConfirmWritingSubmissionRequest;
use App\Http\Requests\EvaluateWritingSubmissionRequest;
use App\Jobs\EvaluateWritingJob;
use App\Models\WritingEvaluation;
use Illuminate\Http\Request;

class WritingSubmissionController extends Controller
{
    public function store(
        StoreWritingSubmissionRequest $request
    ): JsonResponse {
        $question = Question::with('exercise')
            ->findOrFail($request->integer('question_id'));

        if ($question->type !== \App\Enums\QuestionType::WRITING_TEXT) {
            return response()->json([
                'message' => 'Diese Aufgabe ist keine Schreibaufgabe.',
            ], 422);
        }

        $exercise = $question->exercise;

        if (! $exercise || $exercise->status !== 'published') {
            return response()->json([
                'message' => 'Diese Schreibaufgabe ist nicht verfügbar.',
            ], 404);
        }

        $submission = DB::transaction(function () use (
            $request,
            $question,
            $exercise
        ) {
            $data = [
                'user_id' => $request->user()->id,
                'exercise_id' => $exercise->id,
                'question_id' => $question->id,
                'input_method' => $request->input('input_method'),
            ];

            if ($request->input('input_method') === 'text') {
                $data['original_text'] = $request->input('text');
                $data['confirmed_text'] = $request->input('text');
                $data['confirmed_at'] = now();
                $data['status'] = 'ready_for_evaluation';
            }

            if ($request->input('input_method') === 'handwritten_image') {
                $path = $request->file('image')->store(
                    'writing-submissions/' . $request->user()->id,
                    'local'
                );

                $data['image_disk'] = 'local';
                $data['image_path'] = $path;
                $data['status'] = 'extracting';
            }

            return WritingSubmission::create($data);
        });

        return response()->json([
            'message' => 'Schreibantwort wurde gespeichert.',
            'data' => [
                'id' => $submission->id,
                'exercise_id' => $submission->exercise_id,
                'question_id' => $submission->question_id,
                'input_method' => $submission->input_method,
                'status' => $submission->status,
                'original_text' => $submission->original_text,
                'extracted_text' => $submission->extracted_text,
                'confirmed_text' => $submission->confirmed_text,
                'created_at' => $submission->created_at,
            ],
        ], 201);
    }




    public function confirm(
        ConfirmWritingSubmissionRequest $request,
        WritingSubmission $submission
    ): JsonResponse {
        if ($submission->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Sie dürfen diese Schreibantwort nicht bearbeiten.',
            ], 403);
        }

        if ($submission->input_method !== 'handwritten_image') {
            return response()->json([
                'message' => 'Diese Schreibantwort benötigt keine Texterkennung.',
            ], 422);
        }

        if ($submission->status !== 'awaiting_confirmation') {
            return response()->json([
                'message' => 'Der erkannte Text kann derzeit nicht bestätigt werden.',
            ], 422);
        }

        $submission->update([
            'confirmed_text' => $request->validated('confirmed_text'),
            'confirmed_at' => now(),
            'status' => 'ready_for_evaluation',
        ]);

        return response()->json([
            'message' => 'Der erkannte Text wurde bestätigt.',
            'data' => [
                'id' => $submission->id,
                'input_method' => $submission->input_method,
                'extracted_text' => $submission->extracted_text,
                'confirmed_text' => $submission->confirmed_text,
                'status' => $submission->status,
                'confirmed_at' => $submission->confirmed_at,
            ],
        ]);
    }


    public function evaluate(
        EvaluateWritingSubmissionRequest $request,
        WritingSubmission $submission
    ): JsonResponse {
        if ($submission->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Sie dürfen diese Schreibantwort nicht bewerten.',
            ], 403);
        }

        $feedbackLanguage = $request->validated('feedback_language');

        $result = DB::transaction(function () use (
            $submission,
            $request,
            $feedbackLanguage
        ) {
            $lockedSubmission = WritingSubmission::query()
                ->with('user.language')
                ->lockForUpdate()
                ->findOrFail($submission->id);

            if ($lockedSubmission->user_id !== $request->user()->id) {
                return [
                    'error' => true,
                    'status' => 403,
                    'message' => 'Sie dürfen diese Schreibantwort nicht bewerten.',
                ];
            }

            if ($lockedSubmission->status !== 'ready_for_evaluation') {
                return [
                    'error' => true,
                    'status' => 422,
                    'message' => 'Diese Schreibantwort ist noch nicht bereit für die Bewertung.',
                ];
            }

            $language = $feedbackLanguage
                ?? $lockedSubmission->user?->language?->code
                ?? 'de';

            $lockedSubmission->update([
                'status' => 'evaluating',
            ]);

            $evaluation = WritingEvaluation::create([
                'writing_submission_id' => $lockedSubmission->id,
                'feedback_language' => $language,
                'status' => 'pending',
            ]);

            return [
                'error' => false,
                'evaluation' => $evaluation,
            ];
        });

        if ($result['error']) {
            return response()->json([
                'message' => $result['message'],
            ], $result['status']);
        }

        /** @var WritingEvaluation $evaluation */
        $evaluation = $result['evaluation'];

        EvaluateWritingJob::dispatch($evaluation->id);

        return response()->json([
            'message' => 'Die KI-Bewertung wurde gestartet.',
            'data' => [
                'id' => $evaluation->id,
                'writing_submission_id' => $evaluation->writing_submission_id,
                'feedback_language' => $evaluation->feedback_language,
                'status' => $evaluation->status,
            ],
        ], 202);
    }


    public function showEvaluation(
        Request $request,
        WritingEvaluation $evaluation
    ): JsonResponse {
        $evaluation->loadMissing('submission');

        if ($evaluation->submission->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Sie dürfen diese Bewertung nicht ansehen.',
            ], 403);
        }

        return response()->json([
            'data' => [
                'id' => $evaluation->id,
                'writing_submission_id' => $evaluation->writing_submission_id,
                'feedback_language' => $evaluation->feedback_language,
                'status' => $evaluation->status,
                'criteria' => $evaluation->criteria,
                'corrected_text' => $evaluation->corrected_text,
                'improved_example' => $evaluation->improved_example,
                'feedback_de' => $evaluation->feedback_de,
                'feedback_translated' => $evaluation->feedback_translated,
                'errors' => $evaluation->errors,
                'missing_required_points' => $evaluation->missing_required_points,
                'focus_points' => $evaluation->focus_points,
                'evaluated_at' => $evaluation->evaluated_at?->toISOString(),
            ],
        ]);
    }
}

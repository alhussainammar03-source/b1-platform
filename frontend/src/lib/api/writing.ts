import { apiClient } from './client';

export type WritingSubmission = {
  id: number;
  exercise_id: number;
  question_id: number;
  input_method:
    | 'text'
    | 'handwritten_image';
  status: string;
  original_text: string | null;
  extracted_text: string | null;
  confirmed_text: string | null;
  created_at: string;
};

type WritingSubmissionResponse = {
  message: string;
  data: WritingSubmission;
};

export type WritingEvaluationStart = {
  id: number;
  writing_submission_id: number;
  feedback_language: string;
  status: string;
};

type WritingEvaluationStartResponse = {
  message: string;
  data: WritingEvaluationStart;
};

// -------------------------
// AI Evaluation Types
// -------------------------

export type WritingCriterionFeedback = {
  feedback_de: string;
  feedback_translated: string;
};

export type WritingCriteria = {
  task_completion?: WritingCriterionFeedback;
  grammar?: WritingCriterionFeedback;
  spelling?: WritingCriterionFeedback;
  vocabulary?: WritingCriterionFeedback;
  organization?: WritingCriterionFeedback;
};

export type WritingError = {
  original: string;
  correction: string;
  category: string;
  explanation_de: string;
  explanation_translated: string;
};

export type WritingFocusPoint = {
  text_de: string;
  text_translated: string;
};

export type WritingEvaluation = {
  id: number;
  writing_submission_id: number;
  feedback_language: string;
  status: string;

  criteria: WritingCriteria | null;

  corrected_text: string | null;

  improved_example: string | null;

  feedback_de: string | null;

  feedback_translated: string | null;

  errors: WritingError[] | null;

  missing_required_points: string[] | null;

  focus_points:
    | WritingFocusPoint[]
    | null;

  evaluated_at: string | null;
};


export type LatestWritingResult = {
  submission: WritingSubmission;
  evaluation: WritingEvaluation | null;
};

type LatestWritingResponse = {
  data: LatestWritingResult | null;
};


type WritingEvaluationResponse = {
  data: WritingEvaluation;
};

// -------------------------
// Submission
// -------------------------

export async function createTextWritingSubmission(
  questionId: number,
  text: string,
): Promise<WritingSubmission> {
  const response =
    await apiClient.post<WritingSubmissionResponse>(
      '/writing/submissions',
      {
        question_id: questionId,
        input_method: 'text',
        text,
      },
    );

  return response.data.data;
}

// -------------------------
// Start AI Evaluation
// -------------------------

export async function startWritingEvaluation(
  submissionId: number,
  feedbackLanguage?: string,
): Promise<WritingEvaluationStart> {
  const response =
    await apiClient.post<WritingEvaluationStartResponse>(
      `/writing/submissions/${submissionId}/evaluate`,
      feedbackLanguage
        ? {
            feedback_language:
              feedbackLanguage,
          }
        : {},
    );

  return response.data.data;
}

// -------------------------
// Get AI Evaluation
// -------------------------

export async function getWritingEvaluation(
  evaluationId: number,
): Promise<WritingEvaluation> {
  const response =
    await apiClient.get<WritingEvaluationResponse>(
      `/writing/evaluations/${evaluationId}`,
    );

  return response.data.data;
}

export async function getLatestWritingSubmission(
  exerciseId: number,
): Promise<LatestWritingResult | null> {
  const response =
    await apiClient.get<LatestWritingResponse>(
      `/writing/exercises/${exerciseId}/latest`,
    );

  return response.data.data;
}
import { apiClient } from './client';

export type WritingSubmission = {
  id: number;
  exercise_id: number;
  question_id: number;
  input_method: 'text' | 'handwritten_image';
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

export type WritingEvaluation = {
  id: number;
  writing_submission_id: number;
  feedback_language: string;
  status: string;

  criteria: unknown | null;
  corrected_text: string | null;
  improved_example: string | null;

  feedback_de: string | null;
  feedback_translated: string | null;

  errors: unknown | null;
  missing_required_points: unknown | null;
  focus_points: unknown | null;

  evaluated_at: string | null;
};

type WritingEvaluationResponse = {
  data: WritingEvaluation;
};

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

export async function startWritingEvaluation(
  submissionId: number,
  feedbackLanguage?: string,
): Promise<WritingEvaluationStart> {
  const response =
    await apiClient.post<WritingEvaluationStartResponse>(
      `/writing/submissions/${submissionId}/evaluate`,
      feedbackLanguage
        ? {
            feedback_language: feedbackLanguage,
          }
        : {},
    );

  return response.data.data;
}

export async function getWritingEvaluation(
  evaluationId: number,
): Promise<WritingEvaluation> {
  const response =
    await apiClient.get<WritingEvaluationResponse>(
      `/writing/evaluations/${evaluationId}`,
    );

  return response.data.data;
}
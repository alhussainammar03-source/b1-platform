import { apiClient } from './client';


export type ExerciseListPart = {
  id: number;
  key: string;
  title: string;
  sort_order: number;
};

export type ExerciseListItem = {
  id: number;
  key: string;
  type: string;
  title: string;
  description: string | null;
  difficulty: string | null;
  access_level: string;
  sort_order: number;
  section: {
    key: string;
  } | null;
  part: ExerciseListPart | null;
};

type ExerciseListResponse = {
  data: ExerciseListItem[];
};


export type ExerciseOption = {
  id: number;
  text: string;
};

export type ExerciseQuestion = {
  id: number;
  type: string;
  prompt: string;
  instructions: string | null;
  points: string;
  options: ExerciseOption[];
};

export type ExerciseStimulus = {
  id: number;
  type: string;
  label: string | null;
  content: string | null;
  media_file_id: number | null;
  media: unknown | null;
  meta: unknown | null;
};

export type Exercise = {
  id: number;
  key: string;
  type: string;
  title: string;
  description: string | null;
  instructions: string | null;
  difficulty: string | null;
  access_level: string;
  stimuli: ExerciseStimulus[];
  questions: ExerciseQuestion[];
};

type ExerciseResponse = {
  data: Exercise;
};

export async function getExercise(key: string): Promise<Exercise> {
  const response = await apiClient.get<ExerciseResponse>(
    `/exercises/${key}`,
  );

  return response.data.data;
}

export async function getExercises(
  section?: string,
): Promise<ExerciseListItem[]> {
  const response = await apiClient.get<ExerciseListResponse>(
    '/exercises',
    {
      params: section ? { section } : undefined,
    },
  );

  return response.data.data;
}

export type Attempt = {
  id: number;
  exercise_id: number;
  status: string;
  started_at: string;
};

type StartAttemptResponse = {
  message: string;
  data: Attempt;
};

export type SubmitAnswer = {
  question_id: number;
  answer_option_id: number | null;
};

export type GradedAnswer = {
  question_id: number;
  selected_option_id: number | null;
  is_correct: boolean | null;
  awarded_points: number | null;
  max_points: number;
  correct_option: ExerciseOption | null;
};

export type AttemptResult = {
  attempt_id: number;
  status: string;
  score: number;
  max_score: number;
  percentage: number;
  answers: GradedAnswer[];
};

type SubmitAttemptResponse = {
  message: string;
  data: AttemptResult;
};

export async function startAttempt(
  exerciseKey: string,
): Promise<Attempt> {
  const response = await apiClient.post<StartAttemptResponse>(
    `/exercises/${exerciseKey}/attempts`,
  );

  return response.data.data;
}

export async function submitAttempt(
  attemptId: number,
  answers: SubmitAnswer[],
): Promise<AttemptResult> {
  const response = await apiClient.post<SubmitAttemptResponse>(
    `/attempts/${attemptId}/submit`,
    {
      answers,
    },
  );

  return response.data.data;
}
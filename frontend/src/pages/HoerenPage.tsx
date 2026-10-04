import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useTranslation } from 'react-i18next';

import {
  getExercise,
  getExercises,
  startAttempt,
  submitAttempt,
  type AttemptResult,
} from '../lib/api/exercises';

export default function HoerenPage() {
  const { i18n } = useTranslation();

  const locale =
    i18n.resolvedLanguage ?? i18n.language ?? 'de';

  const [selectedExerciseKey, setSelectedExerciseKey] =
    useState<string | null>(null);

  const [answers, setAnswers] =
    useState<Record<number, number>>({});

  const [result, setResult] =
    useState<AttemptResult | null>(null);

  const [isSubmitting, setIsSubmitting] =
    useState(false);

  const [submitError, setSubmitError] =
    useState<string | null>(null);

  const exercisesQuery = useQuery({
    queryKey: ['exercises', 'listening', locale],
    queryFn: () => getExercises('listening'),
  });

  const exerciseQuery = useQuery({
    queryKey: ['exercise', selectedExerciseKey, locale],
    queryFn: () => getExercise(selectedExerciseKey!),
    enabled: selectedExerciseKey !== null,
  });

  const selectExercise = (key: string) => {
    setSelectedExerciseKey(key);
    setAnswers({});
    setResult(null);
    setSubmitError(null);
  };

  const goBack = () => {
    setSelectedExerciseKey(null);
    setAnswers({});
    setResult(null);
    setSubmitError(null);
  };

  if (exercisesQuery.isLoading) {
    return <p>Hörübungen werden geladen...</p>;
  }

  if (exercisesQuery.isError) {
    return (
      <p>
        Die Hörübungen konnten nicht geladen werden.
      </p>
    );
  }

  if (!selectedExerciseKey) {
    return (
      <section className="section-page">
        <p className="section-eyebrow">
          B1 Prüfungstraining
        </p>

        <h1>Hören</h1>

        <p>Wählen Sie einen Teil aus.</p>

        <div className="reading-exercise-list">
          {exercisesQuery.data?.map((exercise) => (
            <button
              key={exercise.id}
              type="button"
              className="reading-exercise-card"
              onClick={() => selectExercise(exercise.key)}
            >
              <strong>
                {exercise.part?.title ?? 'Hören'}
              </strong>

              <span>{exercise.title}</span>

              {exercise.description && (
                <span>{exercise.description}</span>
              )}
            </button>
          ))}
        </div>
      </section>
    );
  }

  if (exerciseQuery.isLoading) {
    return <p>Übung wird geladen...</p>;
  }

  if (exerciseQuery.isError || !exerciseQuery.data) {
    return (
      <section className="section-page">
        <button
          type="button"
          onClick={goBack}
        >
          ← Zurück
        </button>

        <p>
          Die Übung konnte nicht geladen werden.
        </p>
      </section>
    );
  }

  const exercise = exerciseQuery.data;

  const audioStimuli = exercise.stimuli.filter(
    (stimulus) =>
      stimulus.type === 'audio' &&
      stimulus.media !== null,
  );
const unlinkedQuestions = exercise.questions.filter(
  (question) =>
    question.exercise_stimulus_id === null,
);
  const handleSubmit = async () => {
    if (
      Object.keys(answers).length !==
      exercise.questions.length
    ) {
      setSubmitError(
        'Bitte beantworten Sie alle Aufgaben.',
      );

      return;
    }

    setIsSubmitting(true);
    setSubmitError(null);

    try {
      const attempt =
        await startAttempt(exercise.key);

      const submittedAnswers =
        exercise.questions.map((question) => ({
          question_id: question.id,
          answer_option_id:
            answers[question.id] ?? null,
        }));

      const attemptResult =
        await submitAttempt(
          attempt.id,
          submittedAnswers,
        );

      setResult(attemptResult);
    } catch {
      setSubmitError(
        'Die Antworten konnten nicht geprüft werden.',
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <section className="section-page">
      <button
        type="button"
        onClick={goBack}
      >
        ← Zurück
      </button>

      <p className="section-eyebrow">
        {exercise.type}
      </p>

      <h1>{exercise.title}</h1>

      {exercise.description && (
        <p>{exercise.description}</p>
      )}

      {exercise.instructions && (
        <p>{exercise.instructions}</p>
      )}

      <div>
  {audioStimuli.map((stimulus) => {
    const stimulusQuestions =
      exercise.questions.filter(
        (question) =>
          question.exercise_stimulus_id ===
          stimulus.id,
      );

    if (stimulusQuestions.length === 0) {
      return null;
    }

    return (
      <div key={stimulus.id}>
        {stimulus.label && (
          <h3>{stimulus.label}</h3>
        )}

        {stimulus.media && (
          <audio
            controls
            preload="metadata"
            src={stimulus.media.url}
          />
        )}

        {stimulusQuestions.map((question) => {
          const questionIndex =
            exercise.questions.findIndex(
              (item) => item.id === question.id,
            );

          const questionResult =
            result?.answers.find(
              (answer) =>
                answer.question_id ===
                question.id,
            );

          return (
            <div key={question.id}>
              <h3>
                Aufgabe {questionIndex + 1}
              </h3>

              <p>{question.prompt}</p>

              {question.instructions && (
                <p>{question.instructions}</p>
              )}

              <div>
                {question.options.map(
                  (option, optionIndex) => (
                    <label
                      key={option.id}
                      style={{
                        display: 'block',
                        marginBottom: '8px',
                      }}
                    >
                      <input
                        type="radio"
                        name={`question-${question.id}`}
                        value={option.id}
                        checked={
                          answers[question.id] ===
                          option.id
                        }
                        disabled={result !== null}
                        onChange={() => {
                          setAnswers(
                            (currentAnswers) => ({
                              ...currentAnswers,
                              [question.id]:
                                option.id,
                            }),
                          );
                        }}
                      />

                      {' '}
                      {String.fromCharCode(
                        97 + optionIndex,
                      )}
                      ) {option.text}
                    </label>
                  ),
                )}
              </div>

              {questionResult && (
                <p>
                  {questionResult.is_correct
                    ? 'Richtig ✓'
                    : 'Falsch ✗'}
                </p>
              )}

              {questionResult &&
                !questionResult.is_correct &&
                questionResult.correct_option && (
                  <p>
                    Richtige Antwort:{' '}
                    {
                      questionResult
                        .correct_option.text
                    }
                  </p>
                )}
            </div>
          );
        })}
      </div>
    );
  })}



{unlinkedQuestions.length > 0 && (
  <div>
    <div>
      {audioStimuli.map((stimulus) => (
        <div key={`unlinked-audio-${stimulus.id}`}>
          {stimulus.label && (
            <h3>{stimulus.label}</h3>
          )}

          {stimulus.media && (
            <audio
              controls
              preload="metadata"
              src={stimulus.media.url}
            />
          )}
        </div>
      ))}
    </div>

    {unlinkedQuestions.map((question) => {
      const questionIndex =
        exercise.questions.findIndex(
          (item) => item.id === question.id,
        );

      const questionResult =
        result?.answers.find(
          (answer) =>
            answer.question_id === question.id,
        );

      return (
        <div key={question.id}>
          <h3>
            Aufgabe {questionIndex + 1}
          </h3>

          <p>{question.prompt}</p>

          {question.instructions && (
            <p>{question.instructions}</p>
          )}

          <div>
            {question.options.map(
              (option, optionIndex) => (
                <label
                  key={option.id}
                  style={{
                    display: 'block',
                    marginBottom: '8px',
                  }}
                >
                  <input
                    type="radio"
                    name={`question-${question.id}`}
                    value={option.id}
                    checked={
                      answers[question.id] ===
                      option.id
                    }
                    disabled={result !== null}
                    onChange={() => {
                      setAnswers(
                        (currentAnswers) => ({
                          ...currentAnswers,
                          [question.id]:
                            option.id,
                        }),
                      );
                    }}
                  />

                  {' '}
                  {String.fromCharCode(
                    97 + optionIndex,
                  )}
                  ) {option.text}
                </label>
              ),
            )}
          </div>

          {questionResult && (
            <p>
              {questionResult.is_correct
                ? 'Richtig ✓'
                : 'Falsch ✗'}
            </p>
          )}

          {questionResult &&
            !questionResult.is_correct &&
            questionResult.correct_option && (
              <p>
                Richtige Antwort:{' '}
                {
                  questionResult
                    .correct_option.text
                }
              </p>
            )}
        </div>
      );
    })}
  </div>
)}

</div>

      {submitError && (
        <p>{submitError}</p>
      )}

      <button
        type="button"
        onClick={handleSubmit}
        disabled={
          isSubmitting || result !== null
        }
      >
        {isSubmitting
          ? 'Antworten werden geprüft...'
          : 'Antworten prüfen'}
      </button>

      {result && (
        <div>
          <h2>Ergebnis</h2>

          <p>
            {result.score} /{' '}
            {result.max_score} Punkte
          </p>

          <p>
            {result.percentage} %
          </p>
        </div>
      )}
    </section>
  );
}
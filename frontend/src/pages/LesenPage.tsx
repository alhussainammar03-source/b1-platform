import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';

import {
  getExercise,
  getExercises,
  startAttempt,
  submitAttempt,
  type AttemptResult,
} from '../lib/api/exercises';

import GermanContent from '../components/GermanContent';

export default function LesenPage() {
  const { i18n } = useTranslation();

  const locale =
    i18n.resolvedLanguage ?? i18n.language ?? 'de';

  const [selectedExerciseKey, setSelectedExerciseKey] =
    useState<string | null>(null);

  const [selectedAnswers, setSelectedAnswers] = useState<
    Record<number, number>
  >({});

  const [result, setResult] =
    useState<AttemptResult | null>(null);

  const [isSubmitting, setIsSubmitting] = useState(false);

  const [submitError, setSubmitError] =
    useState<string | null>(null);

  const exercisesQuery = useQuery({
    queryKey: ['exercises', 'reading', locale],
    queryFn: () => getExercises('reading'),
  });

  const exerciseQuery = useQuery({
    queryKey: ['exercise', selectedExerciseKey, locale],
    queryFn: () => getExercise(selectedExerciseKey!),
    enabled: selectedExerciseKey !== null,
  });

  
  if (exercisesQuery.isPending) {
    return (
      <main className="home-page">
        <p>Aufgaben werden geladen...</p>
      </main>
    );
  }

  if (exercisesQuery.isError) {
    return (
      <main className="home-page">
        <h1>Lesen</h1>
        <p>Die Aufgaben konnten nicht geladen werden.</p>
      </main>
    );
  }

  if (!selectedExerciseKey) {
    return (
      <main className="home-page">
        <section className="hero">
          <p className="eyebrow">DTZ · LESEN</p>
          <h1>Lesen üben</h1>
          <p>Wählen Sie einen Teil aus.</p>
        </section>

        <section className="reading-exercise-list">
          {exercisesQuery.data.map((item) => (
            <button
              key={item.id}
              type="button"
              className="reading-exercise-card"
              onClick={() => {
                setSelectedAnswers({});
                setResult(null);
                setSubmitError(null);
                setSelectedExerciseKey(item.key);
              }}
            >
              <strong>
                {item.part?.title ?? item.title}
              </strong>

              <span>{item.title}</span>

              {item.description && (
                <span>{item.description}</span>
              )}
            </button>
          ))}
        </section>
      </main>
    );
  }
  if (exerciseQuery.isPending) {
    return (
      <main className="home-page">
        <p>Aufgabe wird geladen...</p>
      </main>
    );
  }

  if (exerciseQuery.isError) {
    return (
      <main className="home-page">
        <h1>Lesen</h1>
        <p>Die Aufgabe konnte nicht geladen werden.</p>
      </main>
    );
  }

  const exercise = exerciseQuery.data;

  const allQuestionsAnswered = exercise.questions.every(
    (question) => selectedAnswers[question.id] !== undefined,
  );

  async function handleSubmit() {
    if (!allQuestionsAnswered || isSubmitting || result) {
      return;
    }

    try {
      setIsSubmitting(true);
      setSubmitError(null);

      const attempt = await startAttempt(exercise.key);

      const answers = exercise.questions.map((question) => ({
        question_id: question.id,
        answer_option_id: selectedAnswers[question.id],
      }));

      const gradingResult = await submitAttempt(
        attempt.id,
        answers,
      );

      setResult(gradingResult);
    } catch (error) {
      console.error(error);

      setSubmitError(
        'Die Antworten konnten nicht geprüft werden. Bitte versuchen Sie es erneut.',
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="home-page">
      <section className="hero">
        <p className="eyebrow">DTZ · LESEN</p>

        <h1>{exercise.title}</h1>

        {exercise.description && (
          <p>{exercise.description}</p>
        )}
      </section>

      <GermanContent>
        <section className="reading-exercise">
          {exercise.instructions && (
            <p>
              <strong>{exercise.instructions}</strong>
            </p>
          )}

          {exercise.stimuli.map((stimulus) => (
            <article
              key={stimulus.id}
              className="reading-stimulus"
            >
              {stimulus.label && <h2>{stimulus.label}</h2>}

              {stimulus.content && (
                <p style={{ whiteSpace: 'pre-line' }}>
                  {stimulus.content}
                </p>
              )}
            </article>
          ))}

          <section className="reading-questions">
            {exercise.questions.map(
              (question, questionIndex) => {
                const questionResult = result?.answers.find(
                  (answer) =>
                    answer.question_id === question.id,
                );

                return (
                  <article
                    key={question.id}
                    className="reading-question"
                  >
                    <h3>
                      {questionIndex + 1}. {question.prompt}
                    </h3>

                    {question.options.map((option) => {
                      const isSelected =
                        selectedAnswers[question.id] === option.id;

                      const isCorrectOption =
                        questionResult?.correct_option?.id ===
                        option.id;

                      const isWrongSelected =
                        Boolean(result) &&
                        isSelected &&
                        questionResult?.is_correct === false;

                      return (
                        <label key={option.id}>
                          <input
                            type="radio"
                            name={`question-${question.id}`}
                            value={option.id}
                            checked={isSelected}
                            disabled={Boolean(result)}
                            onChange={() =>
                              setSelectedAnswers((current) => ({
                                ...current,
                                [question.id]: option.id,
                              }))
                            }
                          />

                          <span>
                            {option.text}

                            {result && isCorrectOption && (
                              <> ✓</>
                            )}

                            {isWrongSelected && (
                              <> ✗</>
                            )}
                          </span>
                        </label>
                      );
                    })}

                    {questionResult && (
                      <p>
                        {questionResult.is_correct
                          ? 'Richtig ✓'
                          : 'Falsch ✗'}
                      </p>
                    )}
                  </article>
                );
              },
            )}
          </section>

          {!result && (
            <button
              type="button"
              onClick={handleSubmit}
              disabled={!allQuestionsAnswered || isSubmitting}
            >
              {isSubmitting
                ? 'Wird geprüft...'
                : 'Antworten prüfen'}
            </button>
          )}

          {!allQuestionsAnswered && !result && (
            <p>
              Bitte beantworten Sie zuerst alle Fragen.
            </p>
          )}

          {submitError && (
            <p role="alert">{submitError}</p>
          )}

          {result && (
            <section className="reading-result">
              <h2>Ergebnis</h2>

              <p>
                <strong>
                  {result.score} / {result.max_score} Punkte
                </strong>
              </p>

              <p>
                {result.percentage} %
              </p>
            </section>
          )}
        </section>
      </GermanContent>
    </main>
  );
  
}


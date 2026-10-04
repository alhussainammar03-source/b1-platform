import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useTranslation } from 'react-i18next';

import {
  getExercise,
  getExercises,
} from '../lib/api/exercises';

import {
  createTextWritingSubmission,
  startWritingEvaluation,
  getWritingEvaluation,
  type WritingSubmission,
  type WritingEvaluationStart,
} from '../lib/api/writing';

export default function SchreibenPage() {
  const { i18n } = useTranslation();

  const locale =
    i18n.resolvedLanguage ??
    i18n.language ??
    'de';

  const [text, setText] = useState('');

  const [submission, setSubmission] =
    useState<WritingSubmission | null>(null);

  const [isSubmitting, setIsSubmitting] =
    useState(false);

  const [submitError, setSubmitError] =
    useState<string | null>(null);

  const [evaluation, setEvaluation] =
    useState<WritingEvaluationStart | null>(
      null,
    );

  const [
    isStartingEvaluation,
    setIsStartingEvaluation,
  ] = useState(false);

  const [
    evaluationError,
    setEvaluationError,
  ] = useState<string | null>(null);

  // -------------------------
  // Übungen laden
  // -------------------------

  const exercisesQuery = useQuery({
    queryKey: [
      'exercises',
      'writing',
      locale,
    ],
    queryFn: () =>
      getExercises('writing'),
  });

  const firstExerciseKey =
    exercisesQuery.data?.[0]?.key ?? null;

  const exerciseQuery = useQuery({
    queryKey: [
      'exercise',
      firstExerciseKey,
      locale,
    ],

    queryFn: () =>
      getExercise(firstExerciseKey!),

    enabled:
      firstExerciseKey !== null,
  });

  // -------------------------
  // KI-Auswertung pollen
  // -------------------------

  const evaluationQuery = useQuery({
    queryKey: [
      'writing-evaluation',
      evaluation?.id,
    ],

    queryFn: () =>
      getWritingEvaluation(
        evaluation!.id,
      ),

    enabled: evaluation !== null,

    refetchInterval: (query) => {
      const status =
        query.state.data?.status;

      if (
        status === 'evaluated' ||
        status === 'failed'
      ) {
        return false;
      }

      return 2000;
    },
  });

  // -------------------------
  // Loading / Fehler
  // -------------------------

  if (exercisesQuery.isLoading) {
    return (
      <p>
        Schreibübungen werden geladen...
      </p>
    );
  }

  if (exercisesQuery.isError) {
    return (
      <p>
        Die Schreibübungen konnten
        nicht geladen werden.
      </p>
    );
  }

  if (!firstExerciseKey) {
    return (
      <p>
        Es wurde keine Schreibübung
        gefunden.
      </p>
    );
  }

  if (exerciseQuery.isLoading) {
    return (
      <p>
        Schreibaufgabe wird geladen...
      </p>
    );
  }

  if (
    exerciseQuery.isError ||
    !exerciseQuery.data
  ) {
    return (
      <p>
        Die Schreibaufgabe konnte
        nicht geladen werden.
      </p>
    );
  }

  const exercise =
    exerciseQuery.data;

  const question =
    exercise.questions[0];

  // -------------------------
  // Antwort speichern
  // -------------------------

  const handleSubmit = async () => {
    if (
      !question ||
      !text.trim()
    ) {
      return;
    }

    setIsSubmitting(true);
    setSubmitError(null);

    try {
      const createdSubmission =
        await createTextWritingSubmission(
          question.id,
          text.trim(),
        );

      setSubmission(
        createdSubmission,
      );
    } catch {
      setSubmitError(
        'Die Schreibantwort konnte nicht gespeichert werden.',
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  // -------------------------
  // KI-Auswertung starten
  // -------------------------

  const handleStartEvaluation =
    async () => {
      if (!submission) {
        return;
      }

      setIsStartingEvaluation(true);
      setEvaluationError(null);

      try {
        const startedEvaluation =
          await startWritingEvaluation(
            submission.id,
            locale,
          );

        setEvaluation(
          startedEvaluation,
        );
      } catch {
        setEvaluationError(
          'Die KI-Bewertung konnte nicht gestartet werden.',
        );
      } finally {
        setIsStartingEvaluation(
          false,
        );
      }
    };

  const evaluationResult =
    evaluationQuery.data;

  const feedback =
    locale === 'de'
      ? evaluationResult?.feedback_de
      : evaluationResult
          ?.feedback_translated;

  // -------------------------
  // Render
  // -------------------------

  return (
    <section className="section-page">
      <p className="section-eyebrow">
        B1 Prüfungstraining
      </p>

      <h1>{exercise.title}</h1>

      {exercise.description && (
        <p>
          {exercise.description}
        </p>
      )}

      {exercise.instructions && (
        <p>
          {exercise.instructions}
        </p>
      )}

      {/* Schreibaufgabe */}

      {exercise.stimuli.map(
        (stimulus) => (
          <div key={stimulus.id}>
            {stimulus.label && (
              <h3>
                {stimulus.label}
              </h3>
            )}

            {stimulus.content && (
              <p>
                {stimulus.content}
              </p>
            )}
          </div>
        ),
      )}

      {/* Frage */}

      {question && (
        <div>
          <h2>Aufgabe</h2>

          <p>
            {question.prompt}
          </p>

          {question.instructions && (
            <p>
              {
                question.instructions
              }
            </p>
          )}

          {/* Antwort */}

          <div>
            <h2>Ihre Antwort</h2>

            <textarea
              value={text}
              onChange={(event) =>
                setText(
                  event.target.value,
                )
              }
              placeholder="Schreiben Sie hier Ihre E-Mail..."
              rows={12}
              disabled={
                submission !== null
              }
              lang="de"
              dir="ltr"
            />

            <p>
              {text.trim()
                ? text
                    .trim()
                    .split(/\s+/)
                    .length
                : 0}{' '}
              Wörter
            </p>

            <button
              type="button"
              onClick={handleSubmit}
              disabled={
                !text.trim() ||
                isSubmitting ||
                submission !== null
              }
            >
              {isSubmitting
                ? 'Antwort wird gespeichert...'
                : 'Antwort abgeben'}
            </button>

            {submitError && (
              <p>{submitError}</p>
            )}
          </div>

          {/* Submission */}

          {submission && (
            <div>
              <p>
                ✓ Ihre Antwort wurde
                gespeichert.
              </p>

              <p>
                Status:{' '}
                {submission.status}
              </p>

              {submission.status ===
                'ready_for_evaluation' &&
                !evaluation && (
                  <button
                    type="button"
                    onClick={
                      handleStartEvaluation
                    }
                    disabled={
                      isStartingEvaluation
                    }
                  >
                    {isStartingEvaluation
                      ? 'KI-Bewertung wird gestartet...'
                      : 'Mit KI auswerten'}
                  </button>
                )}

              {evaluationError && (
                <p>
                  {evaluationError}
                </p>
              )}
            </div>
          )}

          {/* Evaluation gestartet */}

          {evaluation && (
            <div>
              <p>
                KI-Bewertung wurde
                gestartet.
              </p>

              {!evaluationResult && (
                <p>
                  Status:{' '}
                  {evaluation.status}
                </p>
              )}
            </div>
          )}

          {/* Evaluation Status */}

          {evaluationResult &&
            evaluationResult.status !==
              'evaluated' && (
              <p>
                Status:{' '}
                {
                  evaluationResult.status
                }
              </p>
            )}

          {/* Fehler */}

          {evaluationResult?.status ===
            'failed' && (
            <p>
              Die KI-Bewertung ist
              fehlgeschlagen.
            </p>
          )}

          {/* KI Ergebnis */}

          {evaluationResult?.status ===
            'evaluated' && (
            <div>
              <h2>KI-Auswertung</h2>

              {/* Feedback in UI-Sprache */}

              {feedback && (
                <div>
                  <h3>Feedback</h3>

                  <p>
                    {feedback}
                  </p>
                </div>
              )}

              {/* Korrigierter deutscher Text */}

              {evaluationResult.corrected_text && (
                <div>
                  <h3>
                    Korrigierter Text
                  </h3>

                  <p
                    lang="de"
                    dir="ltr"
                    style={{
                      whiteSpace:
                        'pre-wrap',
                      textAlign: 'left',
                    }}
                  >
                    {
                      evaluationResult.corrected_text
                    }
                  </p>
                </div>
              )}

              {/* Verbessertes Beispiel */}

              {evaluationResult.improved_example && (
                <div>
                  <h3>
                    Verbessertes Beispiel
                  </h3>

                  <p
                    lang="de"
                    dir="ltr"
                    style={{
                      whiteSpace:
                        'pre-wrap',
                      textAlign: 'left',
                    }}
                  >
                    {
                      evaluationResult.improved_example
                    }
                  </p>
                </div>
              )}
            </div>
          )}
        </div>
      )}
    </section>
  );
}
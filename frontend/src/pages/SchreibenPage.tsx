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
  getLatestWritingSubmission,
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

  // --------------------------------
  // Schreibübungen laden
  // --------------------------------

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

  const exerciseId =
    exerciseQuery.data?.id ?? null;

  // --------------------------------
  // Letzte Schreibantwort laden
  // --------------------------------

  const latestWritingQuery = useQuery({
    queryKey: [
      'latest-writing-submission',
      exerciseId,
    ],

    queryFn: () =>
      getLatestWritingSubmission(
        exerciseId!,
      ),

    enabled: exerciseId !== null,
  });

  const latestResult =
    latestWritingQuery.data ?? null;

  const activeSubmission =
    submission ??
    latestResult?.submission ??
    null;

  const storedEvaluation =
    latestResult?.evaluation ?? null;

  // --------------------------------
  // Neue laufende KI-Auswertung pollen
  // --------------------------------

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

  // Neue Evaluation hat Vorrang.
  // Nach Refresh verwenden wir die
  // gespeicherte Evaluation vom Backend.

  const evaluationResult =
    evaluationQuery.data ??
    storedEvaluation ??
    null;

  // --------------------------------
  // Loading / Fehler
  // --------------------------------

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

  if (latestWritingQuery.isLoading) {
    return (
      <p>
        Letzte Schreibantwort wird
        geladen...
      </p>
    );
  }

  if (latestWritingQuery.isError) {
    return (
      <p>
        Die letzte Schreibantwort
        konnte nicht geladen werden.
      </p>
    );
  }

  const exercise =
    exerciseQuery.data;

  const question =
    exercise.questions[0];

  // --------------------------------
  // Angezeigter Antworttext
  // --------------------------------

  const savedText =
    activeSubmission?.confirmed_text ??
    activeSubmission?.original_text ??
    '';

  const displayedText =
    activeSubmission
      ? savedText
      : text;

  // --------------------------------
  // Antwort speichern
  // --------------------------------

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

  // --------------------------------
  // KI-Auswertung starten
  // --------------------------------

  const handleStartEvaluation =
    async () => {
      if (!activeSubmission) {
        return;
      }

      setIsStartingEvaluation(true);
      setEvaluationError(null);

      try {
        const startedEvaluation =
          await startWritingEvaluation(
            activeSubmission.id,
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

  // --------------------------------
  // Feedback
  // --------------------------------

  const feedback =
    evaluationResult
      ? evaluationResult.feedback_language ===
        'de'
        ? evaluationResult.feedback_de
        : evaluationResult
            .feedback_translated ??
          evaluationResult.feedback_de
      : null;

  // --------------------------------
  // Render
  // --------------------------------

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

      {/* Aufgabenmaterial */}

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

      {/* Aufgabe */}

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
              value={displayedText}
              onChange={(event) =>
                setText(
                  event.target.value,
                )
              }
              placeholder="Schreiben Sie hier Ihre E-Mail..."
              rows={12}
              disabled={
                activeSubmission !== null
              }
              lang="de"
              dir="ltr"
            />

            <p>
              {displayedText.trim()
                ? displayedText
                    .trim()
                    .split(/\s+/)
                    .length
                : 0}{' '}
              Wörter
            </p>

            {!activeSubmission && (
              <button
                type="button"
                onClick={handleSubmit}
                disabled={
                  !text.trim() ||
                  isSubmitting
                }
              >
                {isSubmitting
                  ? 'Antwort wird gespeichert...'
                  : 'Antwort abgeben'}
              </button>
            )}

            {submitError && (
              <p>{submitError}</p>
            )}
          </div>

          {/* Gespeicherte Antwort */}

          {activeSubmission && (
            <div>
              <p>
                ✓ Ihre Antwort wurde
                gespeichert.
              </p>

              <p>
                Status:{' '}
                {
                  activeSubmission.status
                }
              </p>

              {activeSubmission.status ===
                'ready_for_evaluation' &&
                !evaluationResult &&
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

          {/* Neue KI-Auswertung */}

          {evaluation &&
            !evaluationResult && (
              <div>
                <p>
                  KI-Bewertung wurde
                  gestartet.
                </p>

                <p>
                  Status:{' '}
                  {evaluation.status}
                </p>
              </div>
            )}

          {/* Laufender Status */}

          {evaluationResult &&
            evaluationResult.status !==
              'evaluated' &&
            evaluationResult.status !==
              'failed' && (
              <p>
                KI-Status:{' '}
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

              {/* Feedback */}

              {feedback && (
                <div>
                  <h3>Feedback</h3>

                  <p>
                    {feedback}
                  </p>
                </div>
              )}

              {/* Bewertungskriterien */}

              {evaluationResult.criteria && (
                <div>
                  <h3>
                    Bewertungskriterien
                  </h3>

                  {Object.entries(
                    evaluationResult.criteria,
                  ).map(
                    ([
                      key,
                      criterion,
                    ]) => {
                      if (!criterion) {
                        return null;
                      }

                      const criterionTitles: Record<
                        string,
                        string
                      > = {
                        task_completion:
                          'Aufgabenerfüllung',
                        grammar:
                          'Grammatik',
                        spelling:
                          'Rechtschreibung',
                        vocabulary:
                          'Wortschatz',
                        organization:
                          'Aufbau',
                      };

                      const criterionFeedback =
                        evaluationResult.feedback_language ===
                        'de'
                          ? criterion.feedback_de
                          : criterion.feedback_translated ??
                            criterion.feedback_de;

                      return (
                        <div key={key}>
                          <h4>
                            {criterionTitles[
                              key
                            ] ?? key}
                          </h4>

                          <p>
                            {
                              criterionFeedback
                            }
                          </p>
                        </div>
                      );
                    },
                  )}
                </div>
              )}

{/* Fehler und Korrekturen */}

{evaluationResult.errors &&
  evaluationResult.errors.length > 0 && (
    <div>
      <h3>Fehler & Korrekturen</h3>

      {evaluationResult.errors.map(
        (error, index) => {
          const explanation =
            evaluationResult.feedback_language ===
            'de'
              ? error.explanation_de
              : error.explanation_translated ??
                error.explanation_de;

          return (
            <div
              key={`${error.original}-${index}`}
            >
              <p>
                <strong>Fehler:</strong>
              </p>

              <p
                lang="de"
                dir="ltr"
                style={{
                  textAlign: 'left',
                }}
              >
                ❌ {error.original}
              </p>

              <p>
                <strong>Korrektur:</strong>
              </p>

              <p
                lang="de"
                dir="ltr"
                style={{
                  textAlign: 'left',
                }}
              >
                ✓ {error.correction}
              </p>

              <p>
                <strong>Erklärung:</strong>{' '}
                {explanation}
              </p>
            </div>
          );
        },
      )}
    </div>
  )}

  {/* Fehlende Aufgabenpunkte */}

{evaluationResult.missing_required_points &&
  evaluationResult.missing_required_points.length > 0 && (
    <div>
      <h3>Fehlende Aufgabenpunkte</h3>

      <ul>
        {evaluationResult.missing_required_points.map(
          (point, index) => (
            <li key={`${point}-${index}`}>
              {point}
            </li>
          ),
        )}
      </ul>
    </div>
  )}

{/* Lernfokus */}

{evaluationResult.focus_points &&
  evaluationResult.focus_points.length > 0 && (
    <div>
      <h3>Lernfokus</h3>

      <ul>
        {evaluationResult.focus_points.map(
          (point, index) => {
            const focusText =
              evaluationResult.feedback_language === 'de'
                ? point.text_de
                : point.text_translated ??
                  point.text_de;

            return (
              <li key={index}>
                {focusText}
              </li>
            );
          },
        )}
      </ul>
    </div>
  )}
              {/* Korrigierter Text */}

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
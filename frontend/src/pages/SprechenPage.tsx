import { useState } from 'react';
import { apiClient } from '../lib/api/client';
import { getCsrfCookie } from '../lib/api/auth';
type SpeakingTurn = {
  id: number;
  speaker: 'ai' | 'user';
  text: string | null;
  turn_number: number;
  turn_type: string | null;
};

type SpeakingSession = {
  id: number;
  status: string;
  current_part: number;
  covered_topics: Record<string, boolean>;
};

export default function SprechenPage() {
  const [session, setSession] = useState<SpeakingSession | null>(null);
  const [turns, setTurns] = useState<SpeakingTurn[]>([]);
  const [answer, setAnswer] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function startSpeaking() {
    setLoading(true);
    setError(null);

    try {
        await getCsrfCookie();
      const response = await apiClient.post('/speaking/sessions');

      setSession(response.data.session);
      setTurns([response.data.turn]);
      setAnswer('');
    } catch (err) {
      console.error(err);
      setError('Die Sprechprüfung konnte nicht gestartet werden.');
    } finally {
      setLoading(false);
    }
  }

  async function sendAnswer() {
    if (!session || !answer.trim() || loading) {
      return;
    }

    const text = answer.trim();

    setLoading(true);
    setError(null);
    setAnswer('');

    try {
      const response = await apiClient.post(
        `/speaking/sessions/${session.id}/turns`,
        {
          text,
        }
      );

      setSession(response.data.session);

      setTurns((currentTurns) => [
        ...currentTurns,
        response.data.user_turn,
        response.data.ai_turn,
      ]);
    } catch (err) {
      console.error(err);
      setAnswer(text);
      setError('Die Antwort konnte nicht gesendet werden.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <main>
      <h1>Sprechen</h1>

      {!session && (
        <button
          type="button"
          onClick={startSpeaking}
          disabled={loading}
        >
          {loading ? 'Wird gestartet...' : 'Sprechprüfung starten'}
        </button>
      )}

      {error && <p>{error}</p>}

      {session && (
        <>
          <div>
            {turns.map((turn) => (
              <div key={turn.id}>
                <strong>
                  {turn.speaker === 'ai' ? 'Prüfer:' : 'Sie:'}
                </strong>{' '}
                {turn.text}
              </div>
            ))}
          </div>

          {session.status === 'in_progress' && (
            <div>
              <textarea
                value={answer}
                onChange={(event) => setAnswer(event.target.value)}
                placeholder="Ihre Antwort..."
                disabled={loading}
                rows={5}
              />

              <br />

              <button
                type="button"
                onClick={sendAnswer}
                disabled={loading || !answer.trim()}
              >
                {loading ? 'AI antwortet...' : 'Antwort senden'}
              </button>
            </div>
          )}
        </>
      )}
    </main>
  );
}
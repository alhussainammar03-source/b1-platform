import { FormEvent, useState } from 'react';
import { useNavigate } from 'react-router';
import { login } from '../lib/api/auth';

export default function LoginPage() {
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    setError('');
    setIsSubmitting(true);

    try {
      await login({
        email,
        password,
      });

      navigate('/lesen');
    } catch {
      setError('Die Anmeldung ist fehlgeschlagen.');
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="home-page">
      <section className="hero">
        <p className="eyebrow">CODELVA · B1</p>
        <h1>Anmelden</h1>
        <p>Melden Sie sich an, um Ihre Übungen zu speichern.</p>
      </section>

      <form onSubmit={handleSubmit} className="login-form">
        <label>
          E-Mail
          <input
            type="email"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            required
            autoComplete="email"
          />
        </label>

        <label>
          Passwort
          <input
            type="password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            required
            autoComplete="current-password"
          />
        </label>

        {error && <p role="alert">{error}</p>}

        <button type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Anmeldung...' : 'Anmelden'}
        </button>
      </form>
    </main>
  );
}

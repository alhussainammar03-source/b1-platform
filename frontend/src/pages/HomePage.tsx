import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import GermanContent from '../components/GermanContent';

export default function HomePage() {
  const { t } = useTranslation();

  const sections = [
    { key: 'lesen', path: '/lesen', icon: '📖' },
    { key: 'hoeren', path: '/hoeren', icon: '🎧' },
    { key: 'schreiben', path: '/schreiben', icon: '✍️' },
    { key: 'sprechen', path: '/sprechen', icon: '🎤' },
  ];

  return (
    <main className="home-page">
      <section className="hero">
        <p className="eyebrow">CODELVA · B1</p>

        <h1>{t('title')}</h1>

        <p>{t('subtitle')}</p>
      </section>

      <section className="grid">
        {sections.map((section) => (
          <Link
            key={section.key}
            to={section.path}
            className="section-card"
          >
            <article>
              <span className="section-icon">{section.icon}</span>

              <h2>{t(section.key)}</h2>

              <span className="section-arrow">→</span>
            </article>
          </Link>
        ))}
      </section>

      <section className="exam-cta">
        <div>
          <span>DTZ · B1</span>
          <h2>Modellprüfung</h2>
          <p>
            Trainiere eine komplette Prüfung unter realistischen Bedingungen.
          </p>
        </div>

        <Link to="/modellpruefung">
          Modellprüfung starten →
        </Link>
      </section>

      <GermanContent>
        <section className="sample">
          <b>Deutscher Prüfungsinhalt bleibt immer LTR.</b>

          <p>
            Beispiel: Beschreiben Sie das Bild und sprechen Sie über Ihre
            Erfahrungen.
          </p>
        </section>
      </GermanContent>
    </main>
  );
}
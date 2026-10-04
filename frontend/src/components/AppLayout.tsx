import { NavLink, Outlet } from 'react-router';
import { useTranslation } from 'react-i18next';

const languages = ['de', 'ar', 'en', 'tr', 'uk'];

export default function AppLayout() {
  const { i18n } = useTranslation();

  return (
    <div className="app-layout">
      <header className="app-header">
        <NavLink to="/" className="brand">
          <strong>CODELVA</strong>
          <span>B1</span>
        </NavLink>

        <nav className="main-navigation">
          <NavLink to="/lesen">Lesen</NavLink>
          <NavLink to="/hoeren">Hören</NavLink>
          <NavLink to="/schreiben">Schreiben</NavLink>
          <NavLink to="/sprechen">Sprechen</NavLink>
          <NavLink to="/modellpruefung">Modellprüfung</NavLink>
        </nav>

        <select
          aria-label="Sprache"
          value={i18n.language}
          onChange={(event) => i18n.changeLanguage(event.target.value)}
        >
          {languages.map((language) => (
            <option key={language} value={language}>
              {language.toUpperCase()}
            </option>
          ))}
        </select>
      </header>

      <div className="app-content">
        <Outlet />
      </div>
    </div>
  );
}
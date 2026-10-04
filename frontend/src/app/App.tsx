import { Navigate, Route, Routes } from 'react-router';

import AppLayout from '../components/AppLayout';
import HomePage from '../pages/HomePage';
import LesenPage from '../pages/LesenPage';
import HoerenPage from '../pages/HoerenPage';
import SchreibenPage from '../pages/SchreibenPage';
import SprechenPage from '../pages/SprechenPage';
import ModellpruefungPage from '../pages/ModellpruefungPage';
import LoginPage from '../pages/LoginPage';

export default function App() {
  return (
    <Routes>
        
      <Route element={<AppLayout />}>
        <Route index element={<HomePage />} />
        <Route path="login" element={<LoginPage />} />
        <Route path="lesen" element={<LesenPage />} />
        <Route path="hoeren" element={<HoerenPage />} />
        <Route path="schreiben" element={<SchreibenPage />} />
        <Route path="sprechen" element={<SprechenPage />} />
        <Route
          path="modellpruefung"
          element={<ModellpruefungPage />}
        />
      </Route>

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
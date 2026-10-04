import axios from 'axios';
import i18n from '../i18n/i18n';

export const apiClient = axios.create({
  baseURL:
    import.meta.env.VITE_API_URL ??
    'http://localhost:8000/api/v1',

  headers: {
    Accept: 'application/json',
  },

  withCredentials: true,
  withXSRFToken: true,
});

apiClient.interceptors.request.use((config) => {
  const locale = i18n.language?.split('-')[0] ?? 'de';

  config.headers['X-Locale'] = locale;

  return config;
});
import axios from 'axios';
import { apiClient } from './client';

const backendUrl =
  import.meta.env.VITE_BACKEND_URL ?? 'http://127.0.0.1:8000';

export type LoginCredentials = {
  email: string;
  password: string;
};

export async function getCsrfCookie(): Promise<void> {
  await axios.get(`${backendUrl}/sanctum/csrf-cookie`, {
    withCredentials: true,
    headers: {
      Accept: 'application/json',
    },
  });
}

export async function login(credentials: LoginCredentials) {
  await getCsrfCookie();

  const response = await apiClient.post('/login', credentials);

  return response.data;
}

export async function getCurrentUser() {
  const response = await apiClient.get('/me');

  return response.data;
}

export async function logout(): Promise<void> {
  await apiClient.post('/logout');
}
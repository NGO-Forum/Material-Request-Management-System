// src/stores/auth.ts
import { defineStore } from 'pinia';
import router from '@/router'; // ← Fixed: default import
import axiosClient from '@/plugins/axios';

export interface User {
  id: number;
  name: string;
  email: string;
  image_profile?: string | null;
  role?: any;
  department?: any;
  [key: string]: any;
}

export const useAuthStore = defineStore({
  id: 'auth',
  state: () => ({
    user: null as User | null,
    token: localStorage.getItem('token') || null,
    returnUrl: null as string | null,
  }),
  getters: {
    isLoggedIn: (state) => !!state.token && !!state.user,
    currentUser: (state) => state.user,
  },
  actions: {
    async login(email: string, password: string) {
      try {
        const response = await axiosClient.post('/login', { email, password });
        const { token, data: user } = response.data;

        this.token = token;
        this.user = user;

        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));

        router.push(this.returnUrl || '/main/dashboard/default');
      } catch (error: any) {
        const msg = error.response?.data?.message || 'Login failed';
        throw new Error(msg);
      }
    },

    async register(formData: FormData) {
      try {
        const response = await axiosClient.post('/register', formData);
        const { token, data: user } = response.data;

        this.token = token;
        this.user = user;

        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));

        router.push('/main/dashboard/default');
      } catch (error: any) {
        const msg = error.response?.data?.message || error.response?.data || 'Registration failed';
        throw new Error(msg);
      }
    },

    async fetchUser() {
      try {
        const response = await axiosClient.get('/me');
        this.user = response.data;
        localStorage.setItem('user', JSON.stringify(response.data));
      } catch (error) {
        console.error('Failed to fetch user, logging out...');
        this.logout();
      }
    },

    logout() {
      this.user = null;
      this.token = null;
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      axiosClient.post('/logout').catch(() => {});
      router.push('/login');
    },

    initializeAuth() {
      const token = localStorage.getItem('token');
      const userData = localStorage.getItem('user');

      if (token && userData) {
        this.token = token;
        try {
          this.user = JSON.parse(userData);
        } catch (e) {
          this.logout();
        }
      }
    },
  },
});
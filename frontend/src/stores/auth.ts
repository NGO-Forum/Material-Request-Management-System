// src/stores/auth.ts
import { defineStore } from 'pinia';
import router from '@/router';
import axiosClient from '@/plugins/axios';

export interface User {
  id: number;
  name: string;
  email: string;
  image_profile?: string | null;
  role?: { id: number; name: string };
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
    userRole: (state) => state.user?.role?.name || '',
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

        this.redirectAfterLogin();
      } catch (error: any) {
        const msg = error.response?.data?.message || 'Login failed';
        throw new Error(msg);
      }
    },

    async fetchUser() {
      try {
        const response = await axiosClient.get('/me');
        this.user = response.data;
        localStorage.setItem('user', JSON.stringify(response.data));
      } catch (error) {
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
        } catch {
          this.logout();
        }
      }
    },

    redirectAfterLogin() {
      if (!this.user) return router.push('/login');

      if (this.returnUrl) {
        const path = this.returnUrl;
        this.returnUrl = null;
        return router.push(path);
      }

      const role = this.user.role?.name;
      if (role === 'Admin') {
        router.push('/main/dashboard/default');
      } else if (role === 'Manager' || role === 'Employee') {
        router.push('/main/requests/list');
      } else {
        router.push('/main/requests/list'); // default for unknown roles
      }
    },
  },
});

// src/stores/authUser.ts
import { defineStore } from 'pinia';
import { fetchWrapper } from '@/utils/helpers/fetch-wrapper';

const baseUrl = `${import.meta.env.VITE_API_URL}/users`;

export const useUsersStore = defineStore({
  id: 'users',
  state: () => ({
    users: {} as any,
  }),
  actions: {
    async getAll() {
      this.users = { loading: true };
      try {
        const users = await fetchWrapper.get(baseUrl);
        this.users = users;
      } catch (error) {
        this.users = { error };
      }
    },
  },
});

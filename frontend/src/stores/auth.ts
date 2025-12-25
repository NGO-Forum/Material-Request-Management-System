// src/stores/auth.ts
import { defineStore } from "pinia";
import router from "@/router";
import axiosClient from "@/plugins/axios";

export interface Role {
  id: number;
  name: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  image_profile?: string | null;
  role?: Role | null;
  department?: any;
  token?: string;
  phone_number?: string | null;
  address?: string | null;
  created_at?: string;
  last_login_at?: string;
  [key: string]: any;
}

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null as User | null,
    token: localStorage.getItem("token") || null,
    returnUrl: null as string | null,
  }),

  getters: {
    isLoggedIn: (state) => Boolean(state.token && state.user),
    userRole: (state) => state.user?.role?.name || "",
  },

  actions: {
    // LOGIN
    async login(email: string, password: string) {
      try {
        const res = await axiosClient.post("/login", { email, password });
        const token: string = res.data.token;
        const user: User = res.data.data;

        this.token = token;
        this.user = user;

        localStorage.setItem("token", token);
        localStorage.setItem("user", JSON.stringify(user));

        await this.redirectAfterLogin();
      } catch (error: any) {
        const msg = error.response?.data?.message || "Login failed";
        throw new Error(msg);
      }
    },

    // REGISTER
    async register(formData: FormData) {
      try {
        const res = await axiosClient.post("/register", formData);
        const token: string = res.data.token;
        const user: User = res.data.data;

        this.token = token;
        this.user = user;

        localStorage.setItem("token", token);
        localStorage.setItem("user", JSON.stringify(user));

        await this.redirectAfterLogin();

        return res;
      } catch (error: any) {
        const msg = error.response?.data?.message || "Register failed";
        throw new Error(msg);
      }
    },

    // FETCH AUTH USER
    async fetchUser() {
      if (!this.token) return;

      try {
        const res = await axiosClient.get("/me");
        this.user = res.data;
        localStorage.setItem("user", JSON.stringify(res.data));
      } catch {
        await this.logout();
      }
    },

    // LOGOUT
    async logout() {
      try {
        await axiosClient.post("/logout");
      } catch {
        // ignore errors, token might be expired
      }

      this.user = null;
      this.token = null;

      localStorage.removeItem("token");
      localStorage.removeItem("user");

      router.push("/login");
    },

    // RESTORE SESSION
    initializeAuth() {
      const token = localStorage.getItem("token");
      const userData = localStorage.getItem("user");

      if (token && userData) {
        this.token = token;
        try {
          this.user = JSON.parse(userData) as User;
        } catch {
          this.logout();
        }
      }
    },

    // REDIRECT AFTER LOGIN
    async redirectAfterLogin() {
      if (!this.user) {
        router.push("/login");
        return;
      }

      if (this.returnUrl) {
        const path = this.returnUrl;
        this.returnUrl = null;
        router.push(path);
        return;
      }

      const role = this.user.role?.name;

      if (role === "Admin") {
        router.push("/main/dashboard/default");
      } else {
        router.push("/main/requests/list");
      }
    },
  },
});

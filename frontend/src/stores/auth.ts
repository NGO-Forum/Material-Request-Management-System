import { defineStore } from "pinia";
import router from "@/router";
import axiosClient from "@/plugins/axios";

export interface User {
  id: number;
  name: string;
  email: string;
  image_profile?: string | null;
  role?: { id: number; name: string };
  department?: any;
}

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null as User | null,
    token: (localStorage.getItem("token") as string | null) ?? null,
    returnUrl: null as string | null,
  }),

  getters: {
    isLoggedIn: (s) => Boolean(s.token && s.user),
    userRole: (s) => s.user?.role?.name ?? "",
  },

  actions: {
    // LOGIN
    async login(email: string, password: string): Promise<void> {
      const res = await axiosClient.post("/login", { email, password });

      const token: string = res.data.token;
      const user: User = res.data.data;

      this.token = token;
      this.user = user;

      // store only when token is guaranteed string
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));

      await this.redirectAfterLogin();
    },

    // REGISTER
    async register(formData: FormData) {
      const res = await axiosClient.post("/register", formData);

      const token: string = res.data.token;
      const user: User = res.data.data;

      this.token = token;
      this.user = user;

      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));

      await this.redirectAfterLogin();

      return res;
    },

    // FETCH AUTH USER
    async fetchUser(): Promise<void> {
      try {
        const res = await axiosClient.get("/me");
        this.user = res.data;
        localStorage.setItem("user", JSON.stringify(res.data));
      } catch {
        await this.logout();
      }
    },

    // LOGOUT
    async logout(): Promise<void> {
      try {
        await axiosClient.post("/logout");
      } catch {
        // ignore expired token
      }

      this.user = null;
      this.token = null;

      localStorage.removeItem("token");
      localStorage.removeItem("user");

      router.push("/login");
    },

    // RESTORE SESSION
    initializeAuth(): void {
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
    async redirectAfterLogin(): Promise<void> {
      if (!this.user) {
        router.push("/login");
        return;
      }

      if (this.returnUrl) {
        const redirectPath = this.returnUrl; // narrowed to string
        this.returnUrl = null;
        router.push(redirectPath);
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

// src/plugins/axios.ts
import axios from "axios";
import { useAuthStore } from "@/stores/auth";

const axiosClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL + "/api/v1",
  headers: {
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
  withCredentials: false, // Pure Bearer token mode
});

// INTERCEPTOR: Handle FormData + Add Bearer Token
axiosClient.interceptors.request.use((config) => {
  const authStore = useAuthStore();

  // Fix: Let browser set correct Content-Type for file uploads
  if (config.data instanceof FormData) {
    delete config.headers["Content-Type"];
  }

  // Add Bearer token if exists
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`;
  }

  return config;
});

// INTERCEPTOR: Auto logout on 401
axiosClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      useAuthStore().logout();
    }
    return Promise.reject(error);
  }
);

export default axiosClient;
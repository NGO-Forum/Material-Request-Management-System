import axios from "axios";
import { useAuthStore } from "@/stores/auth";

const axiosClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL + "/api/v1",
  headers: {
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
  withCredentials: false,
});

let isLoggingOut = false;

axiosClient.interceptors.request.use((config) => {
  const auth = useAuthStore();

  // token may be string | null → narrow safely
  const token = auth.token ?? localStorage.getItem("token");

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  if (config.data instanceof FormData) {
    delete config.headers["Content-Type"];
  }

  return config;
});

axiosClient.interceptors.response.use(
  (res) => res,
  async (error) => {
    const status = error.response?.status;
    const url = error.config?.url ?? "";
    const auth = useAuthStore();

    if (status === 401 && !isLoggingOut) {
      const ignore = ["/login", "/register", "/logout"];

      if (!ignore.some((p) => url.includes(p))) {
        isLoggingOut = true;
        try {
          await auth.logout();
        } finally {
          isLoggingOut = false;
        }
      }
    }

    return Promise.reject(error);
  }
);

export default axiosClient;

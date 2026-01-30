import axios from "axios";
import store from "./store";
import apiBaseUrl from "./apiBaseUrl";

const instance = axios.create({
  baseURL: apiBaseUrl,
});

// Add Authorization header before every request
instance.interceptors.request.use(config => {
  const token = localStorage.getItem("access_token"); // always read fresh
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Optional: Response interceptor for 401
instance.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      store.commit("auth/setUser", {}); // clear user
      localStorage.removeItem("access_token");
    }
    return Promise.reject(error);
  }
);

export default instance;

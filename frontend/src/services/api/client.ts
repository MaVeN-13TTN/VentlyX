import axios, { AxiosError } from 'axios';
import type { AxiosInstance, AxiosRequestConfig, InternalAxiosRequestConfig } from 'axios';
import AuthService from './auth'; // Import AuthService

// Define API error shape
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}

// Create a custom axios instance
const apiClient: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000, // 10 seconds
});

// Flag to prevent multiple concurrent refresh attempts
let isRefreshing = false;
let failedQueue: { resolve: (value: unknown) => void; reject: (reason?: any) => void }[] = [];

const processQueue = (error: any, token: string | null = null) => {
  failedQueue.forEach(prom => {
    if (error) {
      prom.reject(error);
    } else {
      prom.resolve(token);
    }
  });
  failedQueue = [];
};

// Add request interceptor to include auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token && config.headers) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Add response interceptor to handle common errors, including token refresh
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };

    // Handle authentication errors (401 Unauthorized)
    if (error.response?.status === 401 && originalRequest && !originalRequest._retry) {
      
      if (isRefreshing) {
        // If already refreshing, queue the original request
        return new Promise(function(resolve, reject) {
          failedQueue.push({ resolve, reject });
        }).then(token => {
          if (originalRequest.headers) {
            originalRequest.headers['Authorization'] = 'Bearer ' + token;
          }
          return apiClient(originalRequest);
        }).catch(err => {
          return Promise.reject(err); // Propagate refresh error
        });
      }

      originalRequest._retry = true;
      isRefreshing = true;

      try {
        const newAccessToken = await AuthService.refreshToken();
        if (newAccessToken) {
          if (originalRequest.headers) {
            originalRequest.headers['Authorization'] = `Bearer ${newAccessToken}`;
          }
          processQueue(null, newAccessToken);
          return apiClient(originalRequest); // Retry original request with new token
        } else {
          // Refresh failed, proceed to logout/redirect
          AuthService.logout(); // Use AuthService logout which clears tokens
          window.location.href = '/login';
          const refreshError = new Error('Unable to refresh token. Logging out.');
          processQueue(refreshError, null);
          return Promise.reject(refreshError);
        }
      } catch (refreshError) {
        // Catch errors during the refresh token call itself
        AuthService.logout(); // Ensure cleanup even if refresh call fails
        window.location.href = '/login';
        processQueue(refreshError, null);
        return Promise.reject(refreshError);
      } finally {
        isRefreshing = false;
      }
    }
    
    // Extract error details (existing logic)
    let apiError: ApiError = {
      message: error.message || 'An unexpected error occurred',
      status: error.response?.status,
    };
    
    if (error.response?.data) {
      const data = error.response.data as any;
      if (data.message) apiError.message = data.message;
      if (data.errors) apiError.errors = data.errors;
    }
    
    // If not a 401 or already retried, reject with the extracted error
    return Promise.reject(apiError);
  }
);

export default apiClient;
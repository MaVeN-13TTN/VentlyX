import axios, { AxiosError } from 'axios';
import type { AxiosInstance, AxiosRequestConfig } from 'axios';

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

// Add response interceptor to handle common errors
apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    // Handle authentication errors
    if (error.response?.status === 401) {
      // Clear storage
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      
      // Redirect to login page
      window.location.href = '/login';
    }
    
    // Extract error details from Laravel validation errors
    let apiError: ApiError = {
      message: error.message || 'An unexpected error occurred',
      status: error.response?.status,
    };
    
    if (error.response?.data) {
      const data = error.response.data as any;
      
      if (data.message) {
        apiError.message = data.message;
      }
      
      if (data.errors) {
        apiError.errors = data.errors;
      }
    }
    
    return Promise.reject(apiError);
  }
);

export default apiClient;
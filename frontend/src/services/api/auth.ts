import apiClient from './client';
import type { ApiError } from './client';

// Define interfaces for authentication
export interface User {
  id: number;
  name: string;
  email: string;
  phone_number?: string;
  profile_picture?: string;
  two_factor_enabled: boolean;
  two_factor_confirmed_at?: string;
  created_at: string;
  updated_at: string;
  roles: Role[];
}

export interface Role {
  id: number;
  name: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegistrationData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone_number?: string;
}

export interface TwoFactorChallengeData {
  code: string;
  recovery_code?: string;
}

export interface AuthResponse {
  user: User;
  token: string;
  two_factor_required?: boolean;
}

export interface PasswordResetRequest {
  email: string;
}

export interface PasswordResetConfirmation {
  email: string;
  token: string;
  password: string;
  password_confirmation: string;
}

export interface ProfileUpdateData {
  name: string;
  phone_number?: string;
}

export interface PasswordChangeData {
  current_password: string;
  password: string;
  password_confirmation: string;
}

export interface TwoFactorSetupResponse {
  qr_code: string;
}

export interface TwoFactorConfirmResponse {
  recovery_codes: string[];
}

class AuthService {
  /**
   * Login a user
   */
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    try {
      const response = await apiClient.post('/auth/login', credentials);
      
      // If 2FA is not required, store token and user directly
      if (!response.data.two_factor_required) {
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
      }
      
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Handle two-factor authentication challenge
   */
  async twoFactorChallenge(data: TwoFactorChallengeData): Promise<AuthResponse> {
    try {
      const response = await apiClient.post('/auth/two-factor-challenge', data);
      
      // Store token and user after successful 2FA
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
      
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Register a new user
   */
  async register(data: RegistrationData): Promise<AuthResponse> {
    try {
      const response = await apiClient.post('/auth/register', data);
      
      // Store token and user after successful registration
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
      
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Logout the current user
   */
  async logout(): Promise<void> {
    try {
      await apiClient.post('/auth/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // Always clear local storage on logout
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
    }
  }

  /**
   * Request a password reset
   */
  async forgotPassword(data: PasswordResetRequest): Promise<void> {
    try {
      await apiClient.post('/auth/forgot-password', data);
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Reset password with token
   */
  async resetPassword(data: PasswordResetConfirmation): Promise<void> {
    try {
      await apiClient.post('/auth/reset-password', data);
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get the current authenticated user
   */
  async getCurrentUser(): Promise<User> {
    try {
      const response = await apiClient.get('/auth/user');
      return response.data.user;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Update user profile
   */
  async updateProfile(data: ProfileUpdateData): Promise<User> {
    try {
      const response = await apiClient.put('/profile', data);
      return response.data.user;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Change user password
   */
  async changePassword(data: PasswordChangeData): Promise<void> {
    try {
      await apiClient.put('/auth/password', data);
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Generate two-factor authentication setup
   */
  async generateTwoFactorSetup(): Promise<TwoFactorSetupResponse> {
    try {
      const response = await apiClient.post('/2fa/enable');
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Confirm two-factor authentication setup
   */
  async confirmTwoFactorSetup(code: string): Promise<TwoFactorConfirmResponse> {
    try {
      const response = await apiClient.post('/2fa/confirm', { code });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Disable two-factor authentication
   */
  async disableTwoFactor(): Promise<void> {
    try {
      await apiClient.post('/2fa/disable');
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Check if the user is authenticated
   */
  isAuthenticated(): boolean {
    return localStorage.getItem('auth_token') !== null;
  }

  /**
   * Get the stored user
   */
  getUser(): User | null {
    const userJson = localStorage.getItem('user');
    return userJson ? JSON.parse(userJson) : null;
  }

  /**
   * Check if user has a specific role
   */
  hasRole(roleName: string): boolean {
    const user = this.getUser();
    return user?.roles?.some(role => role.name === roleName) || false;
  }
}

export default new AuthService();
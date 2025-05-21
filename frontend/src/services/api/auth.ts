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
  refresh_token?: string;
  two_factor_required?: boolean;
}

export interface RefreshResponse {
  token: string;
  refresh_token: string;
  token_type: string;
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

export interface UserPreferences {
  email_notifications: boolean;
  sms_notifications: boolean;
  marketing_emails: boolean;
  public_profile: boolean;
}

export interface ProfileUpdateData {
  name: string;
  phone_number?: string;
  preferences?: UserPreferences;
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

export interface DeleteAccountData {
  password: string;
}

class AuthService {
  /**
   * Login a user
   */
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    try {
      const response = await apiClient.post<AuthResponse>('/auth/login', credentials);

      if (!response.data.two_factor_required) {
        this.storeTokens(response.data.token, response.data.refresh_token);
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
      const response = await apiClient.post<AuthResponse>('/auth/two-factor-challenge', data);

      this.storeTokens(response.data.token, response.data.refresh_token);
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
      const response = await apiClient.post<AuthResponse>('/auth/register', data);

      this.storeTokens(response.data.token, response.data.refresh_token);
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
      // Optionally tell the backend to invalidate the refresh token too
      const refreshToken = this.getRefreshToken();
      if (refreshToken) {
        await apiClient.post('/auth/logout', { refresh_token: refreshToken });
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      this.clearTokens();
      localStorage.removeItem('user');
    }
  }

  /**
   * Refresh the authentication token
   * Handles token rotation by storing the new refresh token
   */
  async refreshToken(): Promise<string | null> {
    const refreshToken = this.getRefreshToken();
    if (!refreshToken) {
      console.warn('No refresh token available');
      return null;
    }

    try {
      console.log('Attempting to refresh token');
      const response = await apiClient.post<RefreshResponse>('/auth/refresh', {
        refresh_token: refreshToken
      });

      const newAccessToken = response.data.token;
      const newRefreshToken = response.data.refresh_token;

      if (!newAccessToken || !newRefreshToken) {
        console.error('Invalid response from refresh endpoint', response.data);
        this.clearTokens();
        localStorage.removeItem('user');
        return null;
      }

      console.log('Token refresh successful');
      // Store both the new access token and refresh token (token rotation)
      this.storeTokens(newAccessToken, newRefreshToken);
      return newAccessToken;
    } catch (error) {
      console.error('Failed to refresh token:', error);
      // Log more details about the error
      if ((error as ApiError).message) {
        console.error('Error message:', (error as ApiError).message);
      }

      this.clearTokens(); // Clear tokens if refresh fails
      localStorage.removeItem('user');
      return null;
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
   * Store authentication tokens
   */
  storeTokens(accessToken: string, refreshToken?: string): void {
    localStorage.setItem('auth_token', accessToken);
    if (refreshToken) {
      localStorage.setItem('refresh_token', refreshToken);
    }
  }

  /**
   * Clear authentication tokens
   */
  clearTokens(): void {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('refresh_token');
  }

  /**
   * Get the stored refresh token
   */
  getRefreshToken(): string | null {
    return localStorage.getItem('refresh_token');
  }

  /**
   * Check if the user is authenticated (checks access token)
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

  /**
   * Upload profile image
   */
  async uploadProfileImage(file: File): Promise<User> {
    try {
      const formData = new FormData();
      formData.append('profile_image', file);

      const response = await apiClient.post('/profile/image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      // Update the stored user with the new profile image
      const user = this.getUser();
      if (user) {
        user.profile_picture = response.data.user.profile_picture;
        localStorage.setItem('user', JSON.stringify(user));
      }

      return response.data.user;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Update user preferences
   */
  async updatePreferences(preferences: UserPreferences): Promise<User> {
    try {
      const response = await apiClient.put('/profile/preferences', { preferences });

      // Update the stored user
      const user = this.getUser();
      if (user) {
        user.preferences = preferences;
        localStorage.setItem('user', JSON.stringify(user));
      }

      return response.data.user;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Delete user account
   */
  async deleteAccount(data: DeleteAccountData): Promise<void> {
    try {
      await apiClient.delete('/profile', { data });
      this.clearTokens();
      localStorage.removeItem('user');
    } catch (error) {
      throw error as ApiError;
    }
  }
}

export default new AuthService();
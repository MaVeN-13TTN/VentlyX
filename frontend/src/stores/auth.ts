import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type {
  User,
  LoginCredentials,
  RegistrationData,
  TwoFactorChallengeData,
  AuthResponse,
  PasswordResetRequest,
  PasswordResetConfirmation,
  ProfileUpdateData,
  PasswordChangeData,
  TwoFactorSetupResponse,
  TwoFactorConfirmResponse,
  UserPreferences,
  DeleteAccountData
} from '@/services/api/auth';
import authService from '@/services/api/auth';

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem('auth_token'));
  const twoFactorRequired = ref(false);

  // Getters
  const isAuthenticated = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.roles.some(role => role.name.toLowerCase() === 'admin') ?? false);
  const isOrganizer = computed(() => user.value?.roles.some(role => role.name.toLowerCase() === 'organizer') ?? false);

  // Actions
  const setUser = (newUser: User | null) => {
    user.value = newUser;
    if (newUser) {
      localStorage.setItem('user', JSON.stringify(newUser));
    } else {
      localStorage.removeItem('user');
    }
  };

  const setToken = (newToken: string | null) => {
    token.value = newToken;
    if (newToken) {
      localStorage.setItem('auth_token', newToken);
    } else {
      localStorage.removeItem('auth_token');
    }
  };

  const setTwoFactorRequired = (required: boolean) => {
    twoFactorRequired.value = required;
  };

  const login = async (credentials: LoginCredentials): Promise<AuthResponse> => {
    const response = await authService.login(credentials);

    if (response.two_factor_required) {
      setTwoFactorRequired(true);
    } else {
      setToken(response.token);
      setUser(response.user);
      setTwoFactorRequired(false);
    }

    return response;
  };

  const register = async (data: RegistrationData): Promise<AuthResponse> => {
    const response = await authService.register(data);
    setToken(response.token);
    setUser(response.user);
    return response;
  };

  const twoFactorChallenge = async (data: TwoFactorChallengeData): Promise<AuthResponse> => {
    const response = await authService.twoFactorChallenge(data);
    setToken(response.token);
    setUser(response.user);
    setTwoFactorRequired(false);
    return response;
  };

  const twoFactorRecovery = async (data: { recovery_code: string }): Promise<AuthResponse> => {
    // Use the twoFactorChallenge method with the recovery code
    return twoFactorChallenge({ code: '', recovery_code: data.recovery_code });
  };

  const logout = async () => {
    try {
      await authService.logout();
    } finally {
      // Clear state even if logout request fails
      setToken(null);
      setUser(null);
      setTwoFactorRequired(false);
    }
  };

  const forgotPassword = async (data: PasswordResetRequest): Promise<void> => {
    await authService.forgotPassword(data);
  };

  const resetPassword = async (data: PasswordResetConfirmation): Promise<void> => {
    await authService.resetPassword(data);
  };

  const updateProfile = async (data: ProfileUpdateData): Promise<User> => {
    const updatedUser = await authService.updateProfile(data);
    setUser(updatedUser);
    return updatedUser;
  };

  const changePassword = async (data: PasswordChangeData): Promise<void> => {
    await authService.changePassword(data);
  };

  const generateTwoFactorSetup = async (): Promise<TwoFactorSetupResponse> => {
    return await authService.generateTwoFactorSetup();
  };

  const confirmTwoFactorSetup = async (code: string): Promise<TwoFactorConfirmResponse> => {
    const response = await authService.confirmTwoFactorSetup(code);
    // Update the user to reflect that 2FA is now enabled
    await fetchCurrentUser();
    return response;
  };

  const disableTwoFactor = async (): Promise<void> => {
    await authService.disableTwoFactor();
    // Update the user to reflect that 2FA is now disabled
    await fetchCurrentUser();
  };

  const fetchCurrentUser = async () => {
    try {
      const currentUser = await authService.getCurrentUser();
      setUser(currentUser);
    } catch (error) {
      // If fetching user fails, assume token is invalid
      setToken(null);
      setUser(null);
      throw error;
    }
  };

  const hasRole = (roleName: string): boolean => {
    return user.value?.roles.some(role => role.name.toLowerCase() === roleName.toLowerCase()) ?? false;
  };

  const uploadProfileImage = async (file: File): Promise<User> => {
    const updatedUser = await authService.uploadProfileImage(file);
    setUser(updatedUser);
    return updatedUser;
  };

  const updatePreferences = async (preferences: UserPreferences): Promise<User> => {
    const updatedUser = await authService.updatePreferences(preferences);
    setUser(updatedUser);
    return updatedUser;
  };

  const deleteAccount = async (password: string): Promise<void> => {
    await authService.deleteAccount({ password });
    setToken(null);
    setUser(null);
  };

  // Initialize user from localStorage if available
  const savedUser = localStorage.getItem('user');
  if (savedUser) {
    try {
      setUser(JSON.parse(savedUser));
    } catch (e) {
      console.error('Failed to parse stored user:', e);
      localStorage.removeItem('user');
    }
  }

  return {
    // State
    user,
    token,
    twoFactorRequired,

    // Getters
    isAuthenticated,
    isAdmin,
    isOrganizer,

    // Actions
    login,
    register,
    logout,
    twoFactorChallenge,
    twoFactorRecovery,
    fetchCurrentUser,
    forgotPassword,
    resetPassword,
    updateProfile,
    changePassword,
    generateTwoFactorSetup,
    confirmTwoFactorSetup,
    disableTwoFactor,
    hasRole,
    uploadProfileImage,
    updatePreferences,
    deleteAccount
  };
});
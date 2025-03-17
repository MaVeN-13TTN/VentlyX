<script setup lang="ts">
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import BaseInput from '@/components/common/BaseInput.vue';
import BaseButton from '@/components/common/BaseButton.vue';
import BaseDialog from '@/components/common/BaseDialog.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const user = computed(() => authStore.user);
const loading = ref(false);
const error = ref('');

// Profile form data
const name = ref(user.value?.name || '');
const email = ref(user.value?.email || '');
const phoneNumber = ref(user.value?.phone_number || '');

// Password change form data
const currentPassword = ref('');
const newPassword = ref('');
const newPasswordConfirmation = ref('');

// 2FA data
const showEnableTwoFactorDialog = ref(false);
const showDisableTwoFactorDialog = ref(false);
const showQrCode = ref(false);
const qrCodeUrl = ref('');
const twoFactorCode = ref('');
const recoveryCodes = ref<string[]>([]);

// Profile update handler
const updateProfile = async () => {
  loading.value = true;
  error.value = '';

  try {
    await authStore.updateProfile({
      name: name.value,
      phone_number: phoneNumber.value || undefined
    });
    
    notificationStore.success('Profile updated successfully');
  } catch (err: any) {
    error.value = err.message || 'Failed to update profile';
  } finally {
    loading.value = false;
  }
};

// Password change handler
const changePassword = async () => {
  if (newPassword.value !== newPasswordConfirmation.value) {
    error.value = 'New passwords do not match';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    await authStore.changePassword({
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: newPasswordConfirmation.value
    });

    // Clear password fields
    currentPassword.value = '';
    newPassword.value = '';
    newPasswordConfirmation.value = '';
    
    notificationStore.success('Password changed successfully');
  } catch (err: any) {
    error.value = err.message || 'Failed to change password';
  } finally {
    loading.value = false;
  }
};

// 2FA handlers
const initiateTwoFactorSetup = async () => {
  loading.value = true;
  error.value = '';

  try {
    const response = await authStore.generateTwoFactorSetup();
    qrCodeUrl.value = response.qr_code;
    showQrCode.value = true;
    showEnableTwoFactorDialog.value = true;
  } catch (err: any) {
    error.value = err.message || 'Failed to setup two-factor authentication';
  } finally {
    loading.value = false;
  }
};

const confirmTwoFactorSetup = async () => {
  loading.value = true;
  error.value = '';

  try {
    const response = await authStore.confirmTwoFactorSetup(twoFactorCode.value);
    recoveryCodes.value = response.recovery_codes;
    showQrCode.value = false;
    twoFactorCode.value = '';
    notificationStore.success('Two-factor authentication enabled successfully');
  } catch (err: any) {
    error.value = err.message || 'Failed to confirm two-factor authentication';
  } finally {
    loading.value = false;
  }
};

const disableTwoFactor = async () => {
  loading.value = true;
  error.value = '';

  try {
    await authStore.disableTwoFactor();
    showDisableTwoFactorDialog.value = false;
    notificationStore.success('Two-factor authentication disabled successfully');
  } catch (err: any) {
    error.value = err.message || 'Failed to disable two-factor authentication';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Account Settings</h1>

      <!-- Error display -->
      <div v-if="error" class="mb-6 rounded-md bg-red-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
          </div>
        </div>
      </div>

      <!-- Profile Section -->
      <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Profile Information</h3>
          <div class="space-y-6">
            <BaseInput
              v-model="name"
              label="Name"
              required
              placeholder="Your full name"
            />

            <BaseInput
              v-model="email"
              type="email"
              label="Email"
              disabled
              placeholder="Your email address"
            />

            <BaseInput
              v-model="phoneNumber"
              type="tel"
              label="Phone Number (optional)"
              placeholder="Your phone number"
            />

            <div class="flex justify-end">
              <BaseButton
                @click="updateProfile"
                :loading="loading"
              >
                Update Profile
              </BaseButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Password Section -->
      <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Change Password</h3>
          <div class="space-y-6">
            <BaseInput
              v-model="currentPassword"
              type="password"
              label="Current Password"
              required
              placeholder="Enter your current password"
            />

            <BaseInput
              v-model="newPassword"
              type="password"
              label="New Password"
              required
              placeholder="Enter your new password"
            />

            <BaseInput
              v-model="newPasswordConfirmation"
              type="password"
              label="Confirm New Password"
              required
              placeholder="Confirm your new password"
            />

            <div class="flex justify-end">
              <BaseButton
                @click="changePassword"
                :loading="loading"
              >
                Change Password
              </BaseButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Two-Factor Authentication Section -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Two-Factor Authentication</h3>
          
          <p class="text-sm text-gray-500 mb-4">
            Add additional security to your account using two-factor authentication.
          </p>

          <template v-if="user?.two_factor_enabled">
            <div class="flex items-center justify-between">
              <div class="text-sm text-green-600">
                <svg class="inline-block h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Two-factor authentication is enabled
              </div>
              <BaseButton
                variant="danger"
                @click="showDisableTwoFactorDialog = true"
              >
                Disable
              </BaseButton>
            </div>
          </template>
          <template v-else>
            <BaseButton
              variant="primary"
              @click="initiateTwoFactorSetup"
              :loading="loading"
            >
              Enable Two-Factor Authentication
            </BaseButton>
          </template>
        </div>
      </div>
    </div>
  </div>

  <!-- Enable 2FA Dialog -->
  <BaseDialog
    v-model="showEnableTwoFactorDialog"
    title="Enable Two-Factor Authentication"
    :prevent-close="loading"
  >
    <template v-if="showQrCode">
      <div class="space-y-4">
        <p class="text-sm text-gray-500">
          Scan the following QR code using your authenticator application.
        </p>

        <div class="flex justify-center">
          <img :src="qrCodeUrl" alt="Two-factor authentication QR code" class="h-48 w-48" />
        </div>

        <BaseInput
          v-model="twoFactorCode"
          label="Verification Code"
          placeholder="Enter the 6-digit code"
        />

        <div class="flex justify-end space-x-3">
          <BaseButton
            variant="outline"
            @click="showEnableTwoFactorDialog = false"
          >
            Cancel
          </BaseButton>
          <BaseButton
            variant="primary"
            @click="confirmTwoFactorSetup"
            :loading="loading"
          >
            Verify & Enable
          </BaseButton>
        </div>
      </div>
    </template>

    <template v-else-if="recoveryCodes.length">
      <div class="space-y-4">
        <p class="text-sm text-gray-500">
          Store these recovery codes in a secure location. They can be used to recover access to your account if your two-factor authentication device is lost.
        </p>

        <div class="bg-gray-50 p-4 rounded-md font-mono text-sm">
          <div v-for="code in recoveryCodes" :key="code" class="mb-1">
            {{ code }}
          </div>
        </div>

        <div class="flex justify-end">
          <BaseButton
            variant="primary"
            @click="showEnableTwoFactorDialog = false"
          >
            Done
          </BaseButton>
        </div>
      </div>
    </template>
  </BaseDialog>

  <!-- Disable 2FA Dialog -->
  <BaseDialog
    v-model="showDisableTwoFactorDialog"
    title="Disable Two-Factor Authentication"
  >
    <p class="text-sm text-gray-500">
      Are you sure you want to disable two-factor authentication? This will decrease the security of your account.
    </p>

    <template #footer>
      <BaseButton
        variant="outline"
        @click="showDisableTwoFactorDialog = false"
      >
        Cancel
      </BaseButton>
      <BaseButton
        variant="danger"
        @click="disableTwoFactor"
        :loading="loading"
      >
        Disable
      </BaseButton>
    </template>
  </BaseDialog>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import BaseInput from '@/components/common/BaseInput.vue';
import BaseButton from '@/components/common/BaseButton.vue';
import BaseDialog from '@/components/common/BaseDialog.vue';

const code = ref('');
const recoveryCode = ref('');
const loading = ref(false);
const error = ref('');
const showRecoveryForm = ref(false);

const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const handleTwoFactorSubmit = async () => {
  if (!code.value) {
    error.value = 'Authentication code is required';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    await authStore.twoFactorChallenge({
      code: code.value
    });
    
    notificationStore.success('Successfully authenticated');
    
    // Redirect to the original intended destination or dashboard
    const redirect = router.currentRoute.value.query.redirect as string;
    router.replace(redirect || { name: 'dashboard' });
  } catch (err: any) {
    error.value = err.message || 'Invalid authentication code';
  } finally {
    loading.value = false;
  }
};

const handleRecoverySubmit = async () => {
  if (!recoveryCode.value) {
    error.value = 'Recovery code is required';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    await authStore.twoFactorRecovery({
      recovery_code: recoveryCode.value
    });
    
    notificationStore.success('Successfully authenticated');
    
    // Redirect to the original intended destination or dashboard
    const redirect = router.currentRoute.value.query.redirect as string;
    router.replace(redirect || { name: 'dashboard' });
  } catch (err: any) {
    error.value = err.message || 'Invalid recovery code';
  } finally {
    loading.value = false;
  }
};

const toggleRecoveryForm = () => {
  showRecoveryForm.value = !showRecoveryForm.value;
  error.value = '';
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Two-Factor Authentication
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          {{ showRecoveryForm ? 
            'Enter a recovery code to authenticate.' :
            'Enter the authentication code from your authenticator app.'
          }}
        </p>
      </div>

      <div v-if="error" class="rounded-md bg-red-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <!-- Error icon -->
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
          </div>
        </div>
      </div>

      <!-- Two-factor authentication code form -->
      <form v-if="!showRecoveryForm" @submit.prevent="handleTwoFactorSubmit" class="mt-8 space-y-6">
        <BaseInput
          v-model="code"
          label="Authentication Code"
          required
          autocomplete="one-time-code"
          placeholder="Enter your 6-digit code"
        />

        <div>
          <BaseButton
            type="submit"
            variant="primary"
            :loading="loading"
            block
          >
            Verify
          </BaseButton>
        </div>

        <div class="text-sm text-center">
          <button
            type="button"
            class="font-medium text-primary-600 hover:text-primary-500"
            @click="toggleRecoveryForm"
          >
            Use a recovery code
          </button>
        </div>
      </form>

      <!-- Recovery code form -->
      <form v-else @submit.prevent="handleRecoverySubmit" class="mt-8 space-y-6">
        <BaseInput
          v-model="recoveryCode"
          label="Recovery Code"
          required
          placeholder="Enter your recovery code"
        />

        <div>
          <BaseButton
            type="submit"
            variant="primary"
            :loading="loading"
            block
          >
            Verify
          </BaseButton>
        </div>

        <div class="text-sm text-center">
          <button
            type="button"
            class="font-medium text-primary-600 hover:text-primary-500"
            @click="toggleRecoveryForm"
          >
            Use authenticator app
          </button>
        </div>
      </form>

      <div class="mt-4 text-center">
        <form @submit.prevent="() => router.push({ name: 'login' })" class="inline">
          <button type="submit" class="text-sm text-gray-600 hover:text-gray-500">
            ← Back to login
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/notification';
import { useAuthStore } from '@/stores/auth';
import BaseInput from '@/components/common/BaseInput.vue';
import BaseButton from '@/components/common/BaseButton.vue';

const email = ref('');
const loading = ref(false);
const error = ref('');

const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const handleSubmit = async () => {
  if (!email.value) {
    error.value = 'Email is required';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    await authStore.forgotPassword({ email: email.value });
    notificationStore.success(
      'Password reset instructions have been sent to your email address.'
    );
    router.push({ name: 'login' });
  } catch (err: any) {
    error.value = err.message || 'Failed to send reset instructions. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-background-dark/30 p-8 rounded-lg shadow-md">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-text-light dark:text-text-dark">
          Reset your password
        </h2>
        <p class="mt-2 text-center text-sm text-text-light/70 dark:text-text-dark/70">
          Enter your email address and we'll send you instructions to reset your password.
        </p>
      </div>

      <div v-if="error" class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <!-- Error icon -->
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">{{ error }}</h3>
          </div>
        </div>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div>
          <BaseInput
            v-model="email"
            type="email"
            label="Email address"
            required
            autocomplete="email"
            :error="error"
            placeholder="Enter your email address"
            class="dark:border-gray-700 dark:bg-background-dark/50 dark:text-text-dark dark:placeholder-gray-400"
          />
        </div>

        <div>
          <BaseButton
            type="submit"
            variant="primary"
            :loading="loading"
            block
            class="bg-gradient-to-r from-primary to-accent-pink hover:from-primary-600 hover:to-accent-pink/90 focus:ring-primary dark:focus:ring-dark-primary"
          >
            Send reset instructions
          </BaseButton>
        </div>

        <div class="text-sm text-center">
          <RouterLink
            to="/login"
            class="font-medium text-primary dark:text-dark-primary hover:text-primary-600 dark:hover:text-dark-primary/80"
          >
            Back to login
          </RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>
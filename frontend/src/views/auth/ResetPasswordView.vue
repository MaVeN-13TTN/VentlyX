<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/notification';
import { useAuthStore } from '@/stores/auth';
import BaseInput from '@/components/common/BaseInput.vue';
import BaseButton from '@/components/common/BaseButton.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const error = ref('');

// Get token from URL
const token = route.query.token as string;

onMounted(() => {
  // Get email from URL if provided
  if (route.query.email) {
    email.value = route.query.email as string;
  }
  
  // Redirect to forgot password if no token
  if (!token) {
    router.replace({ name: 'forgot-password' });
  }
});

const handleSubmit = async () => {
  // Validate inputs
  if (!email.value || !password.value || !passwordConfirmation.value) {
    error.value = 'All fields are required';
    return;
  }

  if (password.value !== passwordConfirmation.value) {
    error.value = 'Passwords do not match';
    return;
  }

  if (password.value.length < 8) {
    error.value = 'Password must be at least 8 characters long';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    await authStore.resetPassword({
      email: email.value,
      token,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    });

    notificationStore.success('Your password has been reset successfully.');
    router.push({ name: 'login' });
  } catch (err: any) {
    if (err.errors) {
      const messages = [];
      for (const field in err.errors) {
        messages.push(...err.errors[field]);
      }
      error.value = messages.join(', ');
    } else {
      error.value = err.message || 'Failed to reset password. Please try again.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Reset your password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Please enter your new password below.
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

      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="space-y-4">
          <BaseInput
            v-model="email"
            type="email"
            label="Email address"
            required
            autocomplete="email"
            :disabled="!!route.query.email"
            placeholder="Enter your email address"
          />

          <BaseInput
            v-model="password"
            type="password"
            label="New password"
            required
            autocomplete="new-password"
            placeholder="Enter your new password"
          />

          <BaseInput
            v-model="passwordConfirmation"
            type="password"
            label="Confirm new password"
            required
            autocomplete="new-password"
            placeholder="Confirm your new password"
          />
        </div>

        <div>
          <BaseButton
            type="submit"
            variant="primary"
            :loading="loading"
            block
          >
            Reset password
          </BaseButton>
        </div>

        <div class="text-sm text-center">
          <RouterLink
            to="/login"
            class="font-medium text-primary-600 hover:text-primary-500"
          >
            Back to login
          </RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>
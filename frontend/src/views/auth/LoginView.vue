<script setup lang="ts">
import { ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useNotificationStore } from "@/stores/notification";

const email = ref("");
const password = ref("");
const remember = ref(false);
const loading = ref(false);
const error = ref("");
const showPassword = ref(false);

// Ensure password visibility toggle works correctly
const togglePasswordVisibility = (e: Event) => {
  e.preventDefault();
  e.stopPropagation();
  showPassword.value = !showPassword.value;
};

const authStore = useAuthStore();
const notificationStore = useNotificationStore();
const router = useRouter();
const route = useRoute();

// Check if we have a redirect URL
const redirectPath = computed(() => (route.query.redirect as string) || "/");

const handleSubmit = async () => {
  if (!email.value || !password.value) {
    error.value = "Email and password are required";
    return;
  }

  loading.value = true;
  error.value = "";

  try {
    const response = await authStore.login({
      email: email.value,
      password: password.value,
      remember: remember.value,
    });

    if (response.two_factor_required) {
      // Redirect to 2FA challenge
      router.push({ name: "two-factor-challenge" });
    } else {
      // Show success notification
      notificationStore.success("Successfully logged in. Welcome back!");

      // Redirect based on user role
      if (route.query.redirect) {
        // If there's a redirect query parameter, use that
        router.push(redirectPath.value);
      } else if (authStore.isAdmin) {
        // Admin users go to admin dashboard
        router.push({ name: "admin-dashboard" });
      } else if (authStore.isOrganizer) {
        // Organizer users go to organizer dashboard
        router.push({ name: "organizer-dashboard" });
      } else {
        // Regular users go to user dashboard
        router.push({ name: "dashboard" });
      }
    }
  } catch (err: any) {
    error.value = err.message || "Failed to login. Please try again.";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="max-w-md mx-auto">
        <div
          class="max-w-md w-full space-y-8 bg-white dark:bg-background-dark/30 p-8 rounded-lg shadow-md"
        >
          <div>
            <h2
              class="mt-6 text-center text-3xl font-extrabold text-text-light dark:text-text-dark"
            >
              Sign in to your account
            </h2>
            <p
              class="mt-2 text-center text-sm text-text-light/70 dark:text-text-dark/70"
            >
              Or
              <RouterLink
                :to="{ name: 'register' }"
                class="font-medium text-primary dark:text-dark-primary hover:text-primary-600 dark:hover:text-dark-primary/80"
              >
                create a new account
              </RouterLink>
            </p>
          </div>

          <div v-if="error" class="bg-red-50 dark:bg-red-900/20 p-4 rounded-md">
            <div class="flex">
              <div class="flex-shrink-0">
                <!-- Error icon -->
                <svg
                  class="h-5 w-5 text-red-400"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  aria-hidden="true"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  {{ error }}
                </h3>
              </div>
            </div>
          </div>

          <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
            <div class="rounded-md shadow-sm -space-y-px">
              <div>
                <label for="email-address" class="sr-only">Email address</label>
                <input
                  id="email-address"
                  name="email"
                  type="email"
                  autocomplete="email"
                  required
                  v-model="email"
                  class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-text-light dark:text-text-dark bg-white dark:bg-background-dark/50 rounded-t-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary focus:z-10 sm:text-sm"
                  placeholder="Email address"
                />
              </div>
              <div class="relative">
                <label for="password" class="sr-only">Password</label>
                <input
                  id="password"
                  name="password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  required
                  v-model="password"
                  class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-text-light dark:text-text-dark bg-white dark:bg-background-dark/50 rounded-b-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary focus:z-10 sm:text-sm pr-10"
                  placeholder="Password"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none z-10"
                  @click="togglePasswordVisibility"
                  tabindex="-1"
                >
                  <!-- Eye icon when password is hidden -->
                  <svg
                    v-if="!showPassword"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                  <!-- Eye-off icon when password is shown -->
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                    />
                  </svg>
                </button>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input
                  id="remember-me"
                  name="remember-me"
                  type="checkbox"
                  v-model="remember"
                  class="h-4 w-4 text-primary dark:text-dark-primary focus:ring-primary dark:focus:ring-dark-primary border-gray-300 dark:border-gray-700 rounded"
                />
                <label
                  for="remember-me"
                  class="ml-2 block text-sm text-text-light dark:text-text-dark"
                >
                  Remember me
                </label>
              </div>

              <div class="text-sm">
                <RouterLink
                  :to="{ name: 'forgot-password' }"
                  class="font-medium text-primary dark:text-dark-primary hover:text-primary-600 dark:hover:text-dark-primary/80"
                >
                  Forgot your password?
                </RouterLink>
              </div>
            </div>

            <div>
              <button
                type="submit"
                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-primary to-accent-pink hover:from-primary-600 hover:to-accent-pink/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-dark-primary transition-all duration-200"
                :disabled="loading"
              >
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                  <!-- Lock icon -->
                  <svg
                    class="h-5 w-5 text-primary-500 group-hover:text-primary-400"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </span>
                <span v-if="loading">Signing in...</span>
                <span v-else>Sign in</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

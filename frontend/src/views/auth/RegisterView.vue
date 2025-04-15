<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useNotificationStore } from "@/stores/notification";

const name = ref("");
const email = ref("");
const password = ref("");
const passwordConfirmation = ref("");
const phoneNumber = ref("");
const loading = ref(false);
const error = ref("");
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Ensure password visibility toggles work correctly
const togglePasswordVisibility = (e: Event) => {
  e.preventDefault();
  e.stopPropagation();
  showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = (e: Event) => {
  e.preventDefault();
  e.stopPropagation();
  showPasswordConfirmation.value = !showPasswordConfirmation.value;
};

const authStore = useAuthStore();
const notificationStore = useNotificationStore();
const router = useRouter();

const handleSubmit = async () => {
  // Basic validation
  if (
    !name.value ||
    !email.value ||
    !password.value ||
    !passwordConfirmation.value
  ) {
    error.value = "All fields are required";
    return;
  }

  if (password.value !== passwordConfirmation.value) {
    error.value = "Passwords do not match";
    return;
  }

  loading.value = true;
  error.value = "";

  try {
    await authStore.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
      phone_number: phoneNumber.value || undefined,
    });

    // Show success notification
    notificationStore.success(
      "Account created successfully! Please log in with your credentials."
    );

    // Redirect to login page after successful registration
    router.push({ name: "login" });
  } catch (err: any) {
    if (err.errors) {
      // Format validation errors
      const messages = [];
      for (const field in err.errors) {
        messages.push(...err.errors[field]);
      }
      error.value = messages.join(", ");
    } else {
      error.value = err.message || "Registration failed. Please try again.";
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div
    class="min-h-screen bg-background-light dark:bg-background-dark flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
  >
    <div
      class="max-w-md w-full space-y-8 bg-white dark:bg-background-dark/30 p-8 rounded-lg shadow-md"
    >
      <div>
        <h2
          class="mt-6 text-center text-3xl font-extrabold text-text-light dark:text-text-dark"
        >
          Create your account
        </h2>
        <p
          class="mt-2 text-center text-sm text-text-light/70 dark:text-text-dark/70"
        >
          Already have an account?
          <RouterLink
            :to="{ name: 'login' }"
            class="font-medium text-primary dark:text-dark-primary hover:text-primary-600 dark:hover:text-dark-primary/80"
          >
            Sign in
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
        <div class="space-y-4">
          <!-- Name -->
          <div>
            <label
              for="name"
              class="block text-sm font-medium text-text-light dark:text-text-dark mb-1"
              >Full Name</label
            >
            <input
              id="name"
              name="name"
              type="text"
              autocomplete="name"
              required
              v-model="name"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-text-light dark:text-text-dark bg-white dark:bg-background-dark/50 rounded-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary sm:text-sm"
              placeholder="Full Name"
            />
          </div>

          <!-- Email -->
          <div>
            <label
              for="register-email"
              class="block text-sm font-medium text-text-light dark:text-text-dark mb-1"
              >Email Address</label
            >
            <input
              id="register-email"
              name="email"
              type="email"
              autocomplete="email"
              required
              v-model="email"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-text-light dark:text-text-dark bg-white dark:bg-background-dark/50 rounded-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary sm:text-sm"
              placeholder="Email address"
            />
          </div>

          <!-- Phone Number (optional) -->
          <div>
            <label
              for="phone-number"
              class="block text-sm font-medium text-text-light dark:text-text-dark mb-1"
              >Phone Number (optional)</label
            >
            <input
              id="phone-number"
              name="phone_number"
              type="tel"
              autocomplete="tel"
              v-model="phoneNumber"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-text-light dark:text-text-dark bg-white dark:bg-background-dark/50 rounded-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary sm:text-sm"
              placeholder="Phone number"
            />
          </div>

          <!-- Password -->
          <div>
            <label
              for="register-password"
              class="block text-sm font-medium text-text-light dark:text-text-dark mb-1"
              >Password</label
            >
            <div class="relative">
              <input
                id="register-password"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                v-model="password"
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-text-light dark:text-text-dark bg-white dark:bg-background-dark/50 rounded-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary sm:text-sm pr-10"
                placeholder="Password (min. 8 characters)"
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

          <!-- Password Confirmation -->
          <div>
            <label
              for="password-confirmation"
              class="block text-sm font-medium text-text-light dark:text-text-dark mb-1"
              >Confirm Password</label
            >
            <div class="relative">
              <input
                id="password-confirmation"
                name="password_confirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                autocomplete="new-password"
                required
                v-model="passwordConfirmation"
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-text-light dark:text-text-dark bg-white dark:bg-background-dark/50 rounded-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary sm:text-sm pr-10"
                placeholder="Confirm password"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none z-10"
                @click="togglePasswordConfirmationVisibility"
                tabindex="-1"
              >
                <!-- Eye icon when password is hidden -->
                <svg
                  v-if="!showPasswordConfirmation"
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
        </div>

        <div>
          <button
            type="submit"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-primary to-accent-pink hover:from-primary-600 hover:to-accent-pink/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-dark-primary transition-all duration-200"
            :disabled="loading"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <!-- User Add icon -->
              <svg
                class="h-5 w-5 text-primary-500 group-hover:text-primary-400"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
              >
                <path
                  d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"
                />
              </svg>
            </span>
            <span v-if="loading">Signing up...</span>
            <span v-else>Create Account</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

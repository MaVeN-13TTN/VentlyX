<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const phoneNumber = ref('');
const loading = ref(false);
const error = ref('');

const authStore = useAuthStore();
const router = useRouter();

const handleSubmit = async () => {
  // Basic validation
  if (!name.value || !email.value || !password.value || !passwordConfirmation.value) {
    error.value = 'All fields are required';
    return;
  }
  
  if (password.value !== passwordConfirmation.value) {
    error.value = 'Passwords do not match';
    return;
  }
  
  loading.value = true;
  error.value = '';
  
  try {
    await authStore.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
      phone_number: phoneNumber.value || undefined
    });
    
    // Redirect to dashboard after successful registration
    router.push({ name: 'dashboard' });
  } catch (err: any) {
    if (err.errors) {
      // Format validation errors
      const messages = [];
      for (const field in err.errors) {
        messages.push(...err.errors[field]);
      }
      error.value = messages.join(', ');
    } else {
      error.value = err.message || 'Registration failed. Please try again.';
    }
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
          Create your account
        </h2>
        <p class="mt-2 text-center text-sm text-text-light/70 dark:text-text-dark/70">
          Already have an account?
          <RouterLink :to="{ name: 'login' }" class="font-medium text-primary dark:text-dark-primary hover:text-primary-600 dark:hover:text-dark-primary/80">
            Sign in
          </RouterLink>
        </p>
      </div>
      
      <div v-if="error" class="bg-red-50 dark:bg-red-900/20 p-4 rounded-md">
        <div class="flex">
          <div class="flex-shrink-0">
            <!-- Error icon -->
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
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
            <label for="name" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Full Name</label>
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
            <label for="register-email" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Email Address</label>
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
            <label for="phone-number" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Phone Number (optional)</label>
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
            <label for="register-password" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Password</label>
            <input 
              id="register-password" 
              name="password" 
              type="password" 
              autocomplete="new-password" 
              required 
              v-model="password"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-text-light dark:text-text-dark bg-white dark:bg-background-dark/50 rounded-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary sm:text-sm" 
              placeholder="Password (min. 8 characters)"
            />
          </div>
          
          <!-- Password Confirmation -->
          <div>
            <label for="password-confirmation" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Confirm Password</label>
            <input 
              id="password-confirmation" 
              name="password_confirmation" 
              type="password" 
              autocomplete="new-password" 
              required 
              v-model="passwordConfirmation"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-text-light dark:text-text-dark bg-white dark:bg-background-dark/50 rounded-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary sm:text-sm" 
              placeholder="Confirm password"
            />
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
              <svg class="h-5 w-5 text-primary-500 group-hover:text-primary-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
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
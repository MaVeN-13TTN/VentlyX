<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAuthStore } from './stores/auth';
import MainLayout from './layouts/MainLayout.vue';

const authStore = useAuthStore();
const isLoading = ref(true);
const hasError = ref(false);

// On app load, try to fetch the current user
onMounted(async () => {
  try {
    if (authStore.isAuthenticated) {
      try {
        await authStore.fetchCurrentUser();
      } catch (error) {
        console.error('Failed to fetch current user:', error);
      }
    }
  } catch (error) {
    console.error('Error initializing application:', error);
    hasError.value = true;
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div v-if="isLoading" class="min-h-screen flex items-center justify-center bg-background-light dark:bg-background-dark">
    <p class="text-text-light dark:text-text-dark text-lg">Loading...</p>
  </div>
  <div v-else-if="hasError" class="min-h-screen flex items-center justify-center bg-background-light dark:bg-background-dark">
    <p class="text-accent-pink text-lg">There was an error loading the application. Please try again later.</p>
  </div>
  <MainLayout v-else>
    <RouterView v-slot="{ Component }">
      <transition name="page" mode="out-in">
        <component :is="Component" />
      </transition>
    </RouterView>
  </MainLayout>
</template>

<style>
/* Define CSS variables for our colors */
:root {
  --primary: #FF6B00;
  --secondary: #FFD700;
  --accent-pink: #FF1493;
  --accent-blue: #00AEEF;
  --bg-light: #FFF4E1;
  --text-light: #222222;
}

/* Dark theme variables */
.dark {
  --primary: #FFB800;
  --secondary: #FF4500;
  --accent-pink: #FF1493;
  --accent-blue: #00BFFF;
  --bg-light: #121212;
  --text-light: #E0E0E0;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  margin: 0;
  padding: 0;
  min-height: 100vh;
  background-color: var(--bg-light);
  color: var(--text-light);
  line-height: 1.6;
  font-size: 16px; /* Increased base font size */
}

#app {
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Global container styles */
.container {
  width: 100%;
  margin-left: auto;
  margin-right: auto;
  padding-left: 1rem;
  padding-right: 1rem;
}

@media (min-width: 640px) {
  .container {
    max-width: 640px;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
}

@media (min-width: 768px) {
  .container {
    max-width: 768px;
  }
}

@media (min-width: 1024px) {
  .container {
    max-width: 1024px;
    padding-left: 2rem;
    padding-right: 2rem;
  }
}

@media (min-width: 1280px) {
  .container {
    max-width: 1280px;
  }
}

/* Increase text sizes */
h1 { font-size: 2.5rem; }
h2 { font-size: 2rem; }
h3 { font-size: 1.75rem; }
h4 { font-size: 1.5rem; }
p { font-size: 1.125rem; }

/* Make form inputs larger */
input, select, textarea {
  font-size: 1.125rem !important;
  padding: 0.75rem 1rem !important;
}

button {
  font-size: 1.125rem !important;
  padding: 0.75rem 1.5rem !important;
}

/* Page transition animations */
.page-enter-active,
.page-leave-active {
  transition: opacity 0.2s ease;
}

.page-enter-from,
.page-leave-to {
  opacity: 0;
}

@media (min-width: 1024px) {
  body {
    font-size: 18px; /* Even larger font size on desktop */
  }
}
</style>

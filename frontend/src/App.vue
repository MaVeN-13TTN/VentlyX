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
    <p class="text-text-light dark:text-text-dark">Loading...</p>
  </div>
  <div v-else-if="hasError" class="min-h-screen flex items-center justify-center bg-background-light dark:bg-background-dark">
    <p class="text-accent-pink">There was an error loading the application. Please try again later.</p>
  </div>
  <MainLayout v-else>
    <RouterView />
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
  
  /* Dark theme variables will be applied via Tailwind's dark mode classes */
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  margin: 0;
  padding: 0;
  min-height: 100vh;
  background-color: var(--bg-light);
  color: var(--text-light);
  line-height: 1.6;
}

/* Rest of existing styles */
header {
  line-height: 1.5;
  max-height: 100vh;
}

.logo {
  display: block;
  margin: 0 auto 2rem;
}

nav {
  width: 100%;
  font-size: 12px;
  text-align: center;
  margin-top: 2rem;
}

nav a.router-link-exact-active {
  color: var(--primary);
}

nav a.router-link-exact-active:hover {
  background-color: transparent;
}

nav a {
  display: inline-block;
  padding: 0 1rem;
  border-left: 1px solid var(--color-border);
}

nav a:first-of-type {
  border: 0;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
    padding-right: calc(var(--section-gap) / 2);
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }

  nav {
    text-align: left;
    margin-left: -1rem;
    font-size: 1rem;

    padding: 1rem 0;
    margin-top: 1rem;
  }
}
</style>

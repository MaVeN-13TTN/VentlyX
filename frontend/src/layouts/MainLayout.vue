<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import ToastNotifications from '@/components/notifications/ToastNotifications.vue';
import Header from '@/components/layout/Header.vue';
import Footer from '@/components/layout/Footer.vue';

const isMenuOpen = ref(false);
const authStore = useAuthStore();
const route = useRoute();

// Check if user is authenticated
const isAuthenticated = computed(() => authStore.isAuthenticated);
// Check if user is admin
const isAdmin = computed(() => authStore.isAdmin);
// Check if user is organizer
const isOrganizer = computed(() => authStore.isOrganizer);
// Get current user
const currentUser = computed(() => authStore.user);

// Detect if we're on the home page for special header treatment
const isHome = computed(() => route.path === '/');

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
  isMenuOpen.value = false;
};

const logout = async () => {
  await authStore.logout();
  // Redirect to home page after logout
  window.location.href = '/';
};

const isActive = (path: string) => {
  return route.path === path;
};

// Mock user for development/testing
onMounted(() => {
  // Simulate login for development
  setTimeout(() => {
    // Properly login through the auth store instead of directly modifying properties
    authStore.login({
      email: 'john@example.com',
      password: 'password123'
    });
    
    // Note: In a real implementation, the login method would handle setting the user
    // This is just for development visualization until the real auth is implemented
  }, 1000);
});

// Watch route changes to add page transition classes
watch(
  () => route.path,
  () => {
    // You could trigger page transition animations here
    window.scrollTo(0, 0); // Scroll to top on page change
  }
);
</script>

<template>
  <div 
    class="min-h-screen flex flex-col bg-background-light dark:bg-background-dark transition-colors duration-300"
    :class="{ 'pt-16': !isHome }"
  >
    <Header 
      :is-home="isHome" 
      :is-authenticated="isAuthenticated" 
      :isAdmin="isAdmin" 
      :isOrganizer="isOrganizer"
      :currentUser="currentUser || {}"
      @logout="logout"
    />
    
    <!-- Main Content Area with smooth page transitions -->
    <main class="flex-grow w-full">
      <router-view v-slot="{ Component }">
        <transition 
          name="page" 
          mode="out-in"
          appear
        >
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
    
    <Footer />
    
    <ToastNotifications />
  </div>
</template>

<style scoped>
/* Page transition animations */
.page-enter-active,
.page-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.page-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
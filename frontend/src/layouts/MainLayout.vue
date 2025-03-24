<script setup lang="ts">
import { ref, computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import ToastNotifications from '@/components/notifications/ToastNotifications.vue';
import ThemeSwitcher from '@/components/common/ThemeSwitcher.vue';
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
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background-light dark:bg-background-dark">
    <!-- Toast Notifications -->
    <ToastNotifications />
    
    <!-- Navigation -->
    <nav class="bg-white/80 dark:bg-background-dark/90 backdrop-blur-md shadow-sm sticky top-0 z-50">
      <div class="container mx-auto px-2 py-3">
        <div class="flex justify-between h-16">
          <!-- Logo and desktop navigation -->
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <RouterLink to="/" class="flex items-center space-x-2 group">
                <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                  <span class="text-white font-bold text-xl">V</span>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">VentlyX</span>
              </RouterLink>
            </div>

            <!-- Desktop navigation links -->
            <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
              <RouterLink to="/" class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 border-b-2" :class="[isActive('/') ? 'border-primary text-primary dark:text-dark-primary dark:border-dark-primary font-semibold' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-primary dark:hover:text-dark-primary hover:border-primary/30 dark:hover:border-dark-primary/30']">
                Home
              </RouterLink>
              
              <RouterLink to="/events" class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 border-b-2" :class="[isActive('/events') ? 'border-primary text-primary dark:text-dark-primary dark:border-dark-primary font-semibold' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-primary dark:hover:text-dark-primary hover:border-primary/30 dark:hover:border-dark-primary/30']">
                Events
              </RouterLink>
              
              <RouterLink to="/about" class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 border-b-2" :class="[isActive('/about') ? 'border-primary text-primary dark:text-dark-primary dark:border-dark-primary font-semibold' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-primary dark:hover:text-dark-primary hover:border-primary/30 dark:hover:border-dark-primary/30']">
                About
              </RouterLink>
              
              <!-- Links for authenticated users -->
              <template v-if="isAuthenticated">
                <RouterLink to="/dashboard" class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 border-b-2" :class="[isActive('/dashboard') ? 'border-primary text-primary dark:text-dark-primary dark:border-dark-primary font-semibold' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-primary dark:hover:text-dark-primary hover:border-primary/30 dark:hover:border-dark-primary/30']">
                  Dashboard
                </RouterLink>
                
                <!-- Links for organizers and admins -->
                <RouterLink v-if="isOrganizer || isAdmin" to="/organizer" class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 border-b-2" :class="[route.path.startsWith('/organizer') ? 'border-primary text-primary dark:text-dark-primary dark:border-dark-primary font-semibold' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-primary dark:hover:text-dark-primary hover:border-primary/30 dark:hover:border-dark-primary/30']">
                  Organizer
                </RouterLink>
                
                <!-- Links for admins only -->
                <RouterLink v-if="isAdmin" to="/admin" class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 border-b-2" :class="[route.path.startsWith('/admin') ? 'border-primary text-primary dark:text-dark-primary dark:border-dark-primary font-semibold' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-primary dark:hover:text-dark-primary hover:border-primary/30 dark:hover:border-dark-primary/30']">
                  Admin
                </RouterLink>
              </template>
            </div>
          </div>

          <!-- User menu and mobile menu button -->
          <div class="flex items-center space-x-3">
            <!-- Theme Switcher -->
            <ThemeSwitcher class="hidden sm:block" />

            <div v-if="isAuthenticated" class="hidden sm:ml-6 sm:flex sm:items-center">
              <!-- Profile dropdown -->
              <div class="ml-3 relative">
                <div>
                  <RouterLink to="/profile" class="flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-dark-primary transition-all duration-200">
                    <span class="sr-only">Open user menu</span>
                    <div v-if="currentUser?.profile_picture" class="h-9 w-9 rounded-full overflow-hidden ring-2 ring-white dark:ring-gray-800">
                      <img class="h-9 w-9 rounded-full object-cover" :src="currentUser.profile_picture" :alt="currentUser.name" />
                    </div>
                    <div v-else class="h-9 w-9 rounded-full bg-gradient-to-br from-primary to-accent-pink flex items-center justify-center text-white ring-2 ring-white dark:ring-gray-800">
                      {{ currentUser?.name?.charAt(0).toUpperCase() }}
                    </div>
                  </RouterLink>
                </div>
              </div>

              <!-- Logout button -->
              <button @click="logout" class="ml-4 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors duration-200 flex items-center space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
              </button>
            </div>
            
            <!-- Login/Register buttons for guests -->
            <div v-else class="hidden sm:flex sm:items-center sm:ml-6 sm:space-x-4">
              <RouterLink to="/login" class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary dark:hover:text-dark-primary transition-colors duration-200">
                Login
              </RouterLink>
              <RouterLink to="/register" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-primary to-accent-pink hover:from-primary-600 hover:to-accent-pink shadow-sm hover:shadow transition-all duration-300">
                Register
              </RouterLink>
            </div>

            <!-- Mobile menu button -->
            <div class="flex items-center sm:hidden">
              <button type="button" @click="toggleMenu" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 dark:text-gray-300 hover:text-primary dark:hover:text-dark-primary hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary dark:focus:ring-dark-primary transition-colors duration-200" aria-controls="mobile-menu" :aria-expanded="isMenuOpen">
                <span class="sr-only">Open main menu</span>
                <!-- Heroicon name: menu (when closed), x (when open) -->
                <svg v-if="!isMenuOpen" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg v-else class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <div v-show="isMenuOpen" id="mobile-menu" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <RouterLink to="/" @click="closeMenu" class="block py-2 px-4 rounded-lg text-base font-medium transition-colors duration-200" :class="[isActive('/') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800']">
            Home
          </RouterLink>
          
          <RouterLink to="/events" @click="closeMenu" class="block py-2 px-4 rounded-lg text-base font-medium transition-colors duration-200" :class="[isActive('/events') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800']">
            Events
          </RouterLink>
          
          <RouterLink to="/about" @click="closeMenu" class="block py-2 px-4 rounded-lg text-base font-medium transition-colors duration-200" :class="[isActive('/about') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800']">
            About
          </RouterLink>
          
          <!-- Links for authenticated users -->
          <template v-if="isAuthenticated">
            <RouterLink to="/dashboard" @click="closeMenu" class="block py-2 px-4 rounded-lg text-base font-medium transition-colors duration-200" :class="[isActive('/dashboard') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800']">
              Dashboard
            </RouterLink>
            
            <!-- Links for organizers and admins -->
            <RouterLink v-if="isOrganizer || isAdmin" to="/organizer" @click="closeMenu" class="block py-2 px-4 rounded-lg text-base font-medium transition-colors duration-200" :class="[route.path.startsWith('/organizer') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800']">
              Organizer
            </RouterLink>
            
            <!-- Links for admins only -->
            <RouterLink v-if="isAdmin" to="/admin" @click="closeMenu" class="block py-2 px-4 rounded-lg text-base font-medium transition-colors duration-200" :class="[route.path.startsWith('/admin') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800']">
              Admin
            </RouterLink>
          </template>
          
          <!-- Theme Switcher in mobile menu -->
          <div class="block py-2 px-4">
            <div class="flex items-center">
              <span class="text-gray-700 dark:text-gray-300 mr-2">Theme:</span>
              <ThemeSwitcher />
            </div>
          </div>
        </div>
        
        <!-- Mobile menu user section -->
        <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-700">
          <div v-if="isAuthenticated">
            <div class="flex items-center px-4 py-2">
              <div class="flex-shrink-0">
                <div v-if="currentUser?.profile_picture" class="h-10 w-10 rounded-full overflow-hidden ring-2 ring-white dark:ring-gray-800">
                  <img class="h-10 w-10 rounded-full object-cover" :src="currentUser.profile_picture" :alt="currentUser.name" />
                </div>
                <div v-else class="h-10 w-10 rounded-full bg-gradient-to-br from-primary to-accent-pink flex items-center justify-center text-white ring-2 ring-white dark:ring-gray-800">
                  {{ currentUser?.name?.charAt(0).toUpperCase() }}
                </div>
              </div>
              <div class="ml-3">
                <div class="text-base font-medium text-gray-800 dark:text-gray-200">{{ currentUser?.name }}</div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ currentUser?.email }}</div>
              </div>
            </div>
            <div class="mt-3 space-y-1 px-2">
              <RouterLink to="/profile" @click="closeMenu" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200">
                Your Profile
              </RouterLink>
              <button @click="logout" class="block w-full text-left px-3 py-2 rounded-lg text-base font-medium text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200">
                Logout
              </button>
            </div>
          </div>
          <!-- Guest options -->
          <div v-else class="mt-3 space-y-1 px-2">
            <RouterLink to="/login" @click="closeMenu" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200">
              Login
            </RouterLink>
            <RouterLink to="/register" @click="closeMenu" class="block px-3 py-2 rounded-lg text-base font-medium bg-gradient-to-r from-primary to-accent-pink text-white mt-2">
              Register
            </RouterLink>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page content -->
    <main class="flex-grow w-full">
      <div class="container mx-auto px-2 py-4">
        <slot></slot>
      </div>
    </main>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<style>
/* ... existing code ... */
</style>
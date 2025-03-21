<script setup lang="ts">
import { ref, computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

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
    <!-- Navigation -->
    <nav class="bg-white/80 dark:bg-background-dark/90 backdrop-blur-md shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
          <div class="flex items-center">
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
    <main class="flex-grow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <slot></slot>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-background-dark/30 border-t border-gray-200 dark:border-gray-800 mt-auto backdrop-blur-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center shadow-sm">
              <span class="text-white font-bold text-base">V</span>
            </div>
            <div class="text-gray-500 dark:text-gray-400 text-sm">
              © {{ new Date().getFullYear() }} VentlyX. All rights reserved.
            </div>
          </div>
          <div class="flex space-x-6">
            <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors duration-200">
              <span class="sr-only">Facebook</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors duration-200">
              <span class="sr-only">Twitter</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors duration-200">
              <span class="sr-only">Instagram</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63z" clip-rule="evenodd" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>
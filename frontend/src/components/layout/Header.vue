<template>
  <header class="bg-white/80 dark:bg-background-dark/80 shadow-sm backdrop-blur-md sticky top-0 z-50 border-b border-gray-100 dark:border-gray-800">
    <div class="container mx-auto px-6">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300 group-hover:scale-105">
            <span class="text-white font-bold text-xl">V</span>
          </div>
          <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">VentlyX</span>
        </router-link>

        <!-- Navigation -->
        <nav class="hidden md:flex items-center space-x-8">
          <router-link 
            to="/" 
            class="relative py-5 text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
            :class="{ 'nav-active': $route.path === '/' }"
          >
            Home
          </router-link>
          
          <router-link 
            to="/events" 
            class="relative py-5 text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
            :class="{ 'nav-active': $route.path.includes('/events') }"
          >
            Events
          </router-link>
          
          <router-link 
            to="/about" 
            class="relative py-5 text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
            :class="{ 'nav-active': $route.path === '/about' }"
          >
            About
          </router-link>
          
          <!-- Links for authenticated users -->
          <template v-if="isAuthenticated">
            <router-link 
              to="/dashboard" 
              class="relative py-5 text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
              :class="{ 'nav-active': $route.path.includes('/dashboard') }"
            >
              Dashboard
            </router-link>
            
            <!-- Links for organizers and admins -->
            <router-link 
              v-if="isOrganizer || isAdmin" 
              to="/organizer" 
              class="relative py-5 text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
              :class="{ 'nav-active': $route.path.includes('/organizer') }"
            >
              Organizer
            </router-link>
            
            <!-- Links for admins only -->
            <router-link 
              v-if="isAdmin" 
              to="/admin" 
              class="relative py-5 text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
              :class="{ 'nav-active': $route.path.includes('/admin') }"
            >
              Admin
            </router-link>
          </template>
        </nav>

        <!-- User Actions -->
        <div class="flex items-center space-x-5">
          <!-- Theme Toggle -->
          <button 
            @click="toggleTheme"
            class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-primary/50 dark:focus:ring-dark-primary/50"
            aria-label="Toggle theme"
          >
            <svg 
              v-if="isDark" 
              class="w-5 h-5 text-dark-primary" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
              />
            </svg>
            <svg 
              v-else 
              class="w-5 h-5 text-primary" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
              />
            </svg>
          </button>

          <!-- Auth Buttons -->
          <template v-if="!isAuthenticated">
            <router-link 
              to="/login" 
              class="hidden md:block text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
            >
              Login
            </router-link>
            <router-link 
              to="/register" 
              class="bg-gradient-to-r from-primary to-accent-pink text-white px-5 py-2 rounded-lg hover:shadow-md transition-all duration-300 font-medium"
            >
              Sign Up
            </router-link>
          </template>

          <!-- User Menu -->
          <div v-else class="relative">
            <button 
              @click="toggleUserMenu"
              class="flex items-center space-x-2 focus:outline-none p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:ring-2 focus:ring-primary/50 dark:focus:ring-dark-primary/50"
            >
              <div v-if="currentUser?.profile_picture" class="w-9 h-9 rounded-full overflow-hidden shadow-sm ring-2 ring-white dark:ring-gray-800">
                <img class="w-9 h-9 rounded-full object-cover" :src="currentUser.profile_picture" :alt="currentUser?.name || 'User profile'" />
              </div>
              <div v-else class="w-9 h-9 bg-gradient-to-br from-primary to-accent-pink rounded-full flex items-center justify-center text-white shadow-sm ring-2 ring-white dark:ring-gray-800">
                {{ currentUser?.name?.charAt(0).toUpperCase() || 'U' }}
              </div>
              <span class="hidden md:block text-text-light dark:text-text-dark font-medium">{{ currentUser?.name || 'User' }}</span>
              <svg 
                class="w-5 h-5 text-text-light/70 dark:text-text-dark/70 hidden md:block" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path 
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <div 
              v-if="isUserMenuOpen"
              class="absolute right-0 mt-3 w-56 bg-white dark:bg-background-dark/90 rounded-lg shadow-lg py-3 border border-gray-100 dark:border-gray-800 backdrop-blur-sm z-50 origin-top-right transform transition-all duration-200 ease-out"
              @click.outside="isUserMenuOpen = false"
            >
              <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800 mb-2">
                <p class="font-medium text-text-light dark:text-text-dark truncate">{{ currentUser?.name }}</p>
                <p class="text-xs text-text-light/60 dark:text-text-dark/60 truncate">{{ currentUser?.email }}</p>
              </div>
              
              <router-link 
                to="/profile"
                class="flex items-center px-4 py-2 text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                @click="isUserMenuOpen = false"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profile
              </router-link>
              
              <router-link 
                to="/tickets"
                class="flex items-center px-4 py-2 text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                @click="isUserMenuOpen = false"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                My Tickets
              </router-link>
              
              <router-link 
                to="/settings"
                class="flex items-center px-4 py-2 text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                @click="isUserMenuOpen = false"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
              </router-link>
              
              <div class="border-t border-gray-100 dark:border-gray-800 my-2"></div>
              
              <button 
                @click="handleLogout"
                class="w-full text-left flex items-center px-4 py-2 text-red-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
              </button>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <button 
            @click="toggleMobileMenu"
            class="md:hidden p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-primary/50 dark:focus:ring-dark-primary/50"
            aria-label="Toggle mobile menu"
          >
            <svg 
              v-if="!isMobileMenuOpen" 
              class="w-6 h-6 text-text-light/80 dark:text-text-dark/80" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg 
              v-else 
              class="w-6 h-6 text-text-light/80 dark:text-text-dark/80" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div 
        v-show="isMobileMenuOpen" 
        class="md:hidden py-3 space-y-0.5 border-t border-gray-100 dark:border-gray-800"
      >
        <router-link 
          to="/"
          class="block py-3 px-4 rounded-lg font-medium transition-all duration-200"
          :class="$route.path === '/' ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800'"
          @click="closeMobileMenu"
        >
          Home
        </router-link>
        
        <router-link 
          to="/events"
          class="block py-3 px-4 rounded-lg font-medium transition-all duration-200"
          :class="$route.path.includes('/events') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800'"
          @click="closeMobileMenu"
        >
          Events
        </router-link>
        
        <router-link 
          to="/about"
          class="block py-3 px-4 rounded-lg font-medium transition-all duration-200"
          :class="$route.path === '/about' ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800'"
          @click="closeMobileMenu"
        >
          About
        </router-link>

        <!-- Links for authenticated users -->
        <template v-if="isAuthenticated">
          <router-link 
            to="/dashboard"
            class="block py-3 px-4 rounded-lg font-medium transition-all duration-200"
            :class="$route.path.includes('/dashboard') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800'"
            @click="closeMobileMenu"
          >
            Dashboard
          </router-link>
          
          <!-- Links for organizers and admins -->
          <router-link 
            v-if="isOrganizer || isAdmin"
            to="/organizer"
            class="block py-3 px-4 rounded-lg font-medium transition-all duration-200"
            :class="$route.path.includes('/organizer') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800'"
            @click="closeMobileMenu"
          >
            Organizer
          </router-link>
          
          <!-- Links for admins only -->
          <router-link 
            v-if="isAdmin"
            to="/admin"
            class="block py-3 px-4 rounded-lg font-medium transition-all duration-200"
            :class="$route.path.includes('/admin') ? 'bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary' : 'text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800'"
            @click="closeMobileMenu"
          >
            Admin
          </router-link>

          <div class="border-t border-gray-100 dark:border-gray-800 my-2"></div>
          
          <button 
            @click="handleLogout"
            class="w-full text-left py-3 px-4 text-red-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors rounded-lg font-medium flex items-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
          </button>
        </template>

        <!-- Guest Options -->
        <template v-else>
          <router-link 
            to="/login"
            class="block py-3 px-4 rounded-lg font-medium transition-all duration-200 text-text-light/80 dark:text-text-dark/80 hover:bg-gray-100 dark:hover:bg-gray-800"
            @click="closeMobileMenu"
          >
            Login
          </router-link>
          
          <router-link 
            to="/register"
            class="block py-3 px-4 mt-2 rounded-lg font-medium bg-gradient-to-r from-primary to-accent-pink text-white transition-all duration-300"
            @click="closeMobileMenu"
          >
            Sign Up
          </router-link>
        </template>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, defineProps, defineEmits, onMounted, watch, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const props = defineProps({
  isAuthenticated: {
    type: Boolean,
    default: false
  },
  isAdmin: {
    type: Boolean,
    default: false
  },
  isOrganizer: {
    type: Boolean,
    default: false
  },
  currentUser: {
    type: Object,
    default: () => ({}),
    required: false
  }
})

const emit = defineEmits(['logout'])

const isDark = ref(false)
const isUserMenuOpen = ref(false)
const isMobileMenuOpen = ref(false)

// Close dropdown on route change
watch(() => route.path, () => {
  isUserMenuOpen.value = false
  isMobileMenuOpen.value = false
})

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (isUserMenuOpen.value && !target.closest('.user-dropdown')) {
    isUserMenuOpen.value = false
  }
}

onMounted(() => {
  // Initialize theme from localStorage or system preference
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
  
  // Add click outside listener
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  // Clean up event listener
  document.removeEventListener('click', handleClickOutside)
})

const toggleTheme = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark')
  // Store preference in localStorage
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const handleLogout = () => {
  isUserMenuOpen.value = false
  isMobileMenuOpen.value = false
  emit('logout')
}
</script>

<style scoped>
.nav-active {
  color: var(--primary-color);
  font-weight: 600;
}

.dark .nav-active {
  color: var(--dark-primary-color);
}

.nav-active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  border-radius: 9999px;
  background: linear-gradient(to right, var(--primary-color), var(--accent-pink-color));
  animation: navIndicatorIn 0.3s forwards ease-out;
  transform-origin: bottom left;
}

@keyframes navIndicatorIn {
  from {
    transform: scaleX(0);
  }
  to {
    transform: scaleX(1);
  }
}

.user-dropdown {
  animation: fadeIn 0.2s forwards ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
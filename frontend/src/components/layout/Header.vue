<template>
  <header class="bg-white dark:bg-background-dark/50 shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-6">
      <div class="flex items-center justify-between h-20">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center shadow-sm">
            <span class="text-white font-bold text-xl">V</span>
          </div>
          <span class="text-2xl font-bold text-primary dark:text-dark-primary">VentlyX</span>
        </router-link>

        <!-- Navigation -->
        <nav class="hidden md:flex items-center space-x-10">
          <router-link 
            to="/" 
            class="text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
          >
            Home
          </router-link>
          
          <router-link 
            to="/events" 
            class="text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
          >
            Events
          </router-link>
          
          <router-link 
            to="/about" 
            class="text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
          >
            About
          </router-link>
          
          <!-- Links for authenticated users -->
          <template v-if="isAuthenticated">
            <router-link 
              to="/dashboard" 
              class="text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
            >
              Dashboard
            </router-link>
            
            <!-- Links for organizers and admins -->
            <router-link 
              v-if="isOrganizer || isAdmin" 
              to="/organizer" 
              class="text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
            >
              Organizer
            </router-link>
            
            <!-- Links for admins only -->
            <router-link 
              v-if="isAdmin" 
              to="/admin" 
              class="text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors font-medium"
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
            class="p-2.5 rounded-full hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors"
            aria-label="Toggle theme"
          >
            <svg 
              v-if="isDark" 
              class="w-6 h-6 text-dark-primary" 
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
              class="w-6 h-6 text-primary" 
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
              class="bg-primary dark:bg-dark-primary text-white px-5 py-2.5 rounded-full hover:bg-primary-600 dark:hover:bg-dark-primary/90 transition-colors font-medium shadow-sm"
            >
              Sign Up
            </router-link>
          </template>

          <!-- User Menu -->
          <div v-else class="relative">
            <button 
              @click="toggleUserMenu"
              class="flex items-center space-x-2 focus:outline-none p-1 rounded-full hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors"
            >
              <div v-if="currentUser?.profile_picture" class="w-10 h-10 rounded-full overflow-hidden shadow-sm">
                <img class="w-10 h-10 rounded-full object-cover" :src="currentUser.profile_picture" :alt="currentUser?.name || 'User profile'" />
              </div>
              <div v-else class="w-10 h-10 bg-gradient-to-br from-accent-pink to-accent-blue rounded-full flex items-center justify-center text-white shadow-sm">
                {{ currentUser?.name?.charAt(0).toUpperCase() || 'U' }}
              </div>
              <span class="hidden md:block text-text-light dark:text-text-dark font-medium">{{ currentUser?.name || 'User' }}</span>
              <svg 
                class="w-5 h-5 text-text-light/70 dark:text-text-dark/70" 
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
              class="absolute right-0 mt-3 w-52 bg-white dark:bg-background-dark/90 rounded-xl shadow-lg py-3 border border-gray-100 dark:border-gray-800"
            >
              <router-link 
                to="/profile"
                class="block px-5 py-2.5 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors"
                @click="isUserMenuOpen = false"
              >
                Profile
              </router-link>
              <router-link 
                to="/tickets"
                class="block px-5 py-2.5 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors"
                @click="isUserMenuOpen = false"
              >
                My Tickets
              </router-link>
              <router-link 
                to="/settings"
                class="block px-5 py-2.5 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors"
                @click="isUserMenuOpen = false"
              >
                Settings
              </router-link>
              <div class="border-t border-gray-100 dark:border-gray-800 my-1"></div>
              <button 
                @click="handleLogout"
                class="w-full text-left px-5 py-2.5 text-accent-pink hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors"
              >
                Logout
              </button>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <button 
            @click="toggleMobileMenu"
            class="md:hidden p-2.5 rounded-full text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors"
            aria-label="Toggle mobile menu"
          >
            <svg 
              v-if="!isMobileMenuOpen" 
              class="w-6 h-6" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg 
              v-else 
              class="w-6 h-6" 
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
          class="block py-3 px-4 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors rounded-lg font-medium"
          @click="closeMobileMenu"
        >
          Home
        </router-link>
        <router-link 
          to="/events"
          class="block py-3 px-4 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors rounded-lg font-medium"
          @click="closeMobileMenu"
        >
          Events
        </router-link>
        <router-link 
          to="/about"
          class="block py-3 px-4 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors rounded-lg font-medium"
          @click="closeMobileMenu"
        >
          About
        </router-link>

        <!-- Links for authenticated users -->
        <template v-if="isAuthenticated">
          <router-link 
            to="/dashboard"
            class="block py-3 px-4 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors rounded-lg font-medium"
            @click="closeMobileMenu"
          >
            Dashboard
          </router-link>
          
          <!-- Links for organizers and admins -->
          <router-link 
            v-if="isOrganizer || isAdmin"
            to="/organizer"
            class="block py-3 px-4 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors rounded-lg font-medium"
            @click="closeMobileMenu"
          >
            Organizer
          </router-link>
          
          <!-- Links for admins only -->
          <router-link 
            v-if="isAdmin"
            to="/admin"
            class="block py-3 px-4 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors rounded-lg font-medium"
            @click="closeMobileMenu"
          >
            Admin
          </router-link>

          <div class="border-t border-gray-100 dark:border-gray-800 my-2"></div>
          
          <button 
            @click="handleLogout"
            class="w-full text-left py-3 px-4 text-accent-pink hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors rounded-lg font-medium"
          >
            Logout
          </button>
        </template>

        <!-- Guest Options -->
        <template v-else>
          <router-link 
            to="/login"
            class="block py-3 px-4 text-text-light/80 dark:text-text-dark/80 hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors rounded-lg font-medium"
            @click="closeMobileMenu"
          >
            Login
          </router-link>
          <router-link 
            to="/register"
            class="block py-3 px-4 text-primary dark:text-dark-primary hover:bg-background-light dark:hover:bg-background-dark/30 transition-colors rounded-lg font-medium"
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
import { ref, defineProps, defineEmits, onMounted } from 'vue'

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

const toggleTheme = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark')
  // Store preference in localStorage
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

// Initialize theme from localStorage or system preference
onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
})

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
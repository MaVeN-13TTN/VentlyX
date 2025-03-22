<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';

const darkMode = ref(false);
const isAnimating = ref(false);

const toggleTheme = () => {
  if (isAnimating.value) return; // Prevent multiple clicks during animation
  
  isAnimating.value = true;
  darkMode.value = !darkMode.value;
  
  // Apply theme to document with transition
  document.documentElement.classList.add('theme-transition');
  
  if (darkMode.value) {
    document.documentElement.classList.add('dark');
    document.body.classList.add('dark');
    localStorage.setItem('theme', 'dark');
  } else {
    document.documentElement.classList.remove('dark');
    document.body.classList.remove('dark');
    localStorage.setItem('theme', 'light');
  }
  
  // Remove transition class after animation completes
  setTimeout(() => {
    document.documentElement.classList.remove('theme-transition');
    isAnimating.value = false;
  }, 500);
};

// Check user's preference on mount
onMounted(() => {
  // Add global CSS for theme transitions
  const style = document.createElement('style');
  style.textContent = `
    .theme-transition,
    .theme-transition *,
    .theme-transition *::before,
    .theme-transition *::after {
      transition: background-color 0.4s ease, color 0.4s ease, border-color 0.4s ease, fill 0.4s ease, stroke 0.4s ease !important;
    }
  `;
  document.head.appendChild(style);

  // Check for saved theme preference
  const savedTheme = localStorage.getItem('theme');
  
  // Check for system preference if no saved preference
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  // Set initial state
  darkMode.value = savedTheme === 'dark' || (!savedTheme && prefersDark);
  
  // Apply initial theme
  if (darkMode.value) {
    document.documentElement.classList.add('dark');
    document.body.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
    document.body.classList.remove('dark');
  }
  
  // Listen for system theme changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    if (!localStorage.getItem('theme')) {
      darkMode.value = e.matches;
      if (darkMode.value) {
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark');
      }
    }
  });
});
</script>

<template>
  <button 
    @click="toggleTheme" 
    type="button" 
    aria-label="Toggle dark mode"
    class="theme-toggle relative inline-flex h-6 w-12 rounded-full focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary focus:ring-offset-2 transition-all duration-300"
    :class="[darkMode ? 'bg-primary/80 dark:bg-dark-primary' : 'bg-gray-200 dark:bg-gray-700']"
  >
    <span class="sr-only">Toggle dark mode</span>
    
    <!-- Toggle Knob with Icons -->
    <span 
      class="toggle-knob flex items-center justify-center w-5 h-5 rounded-full transform transition-all duration-300 ease-spring shadow-md"
      :class="[
        darkMode ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white', 
        isAnimating ? 'scale-90' : 'scale-100'
      ]"
    >
      <!-- Sun icon (shown in dark mode) -->
      <svg 
        v-if="darkMode" 
        xmlns="http://www.w3.org/2000/svg" 
        class="h-3 w-3 text-yellow-500 animate-fadeIn" 
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
      </svg>
      
      <!-- Moon icon (shown in light mode) -->
      <svg 
        v-else 
        xmlns="http://www.w3.org/2000/svg" 
        class="h-3 w-3 text-gray-700 animate-fadeIn" 
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
      </svg>
    </span>
    
    <!-- Background Icons (for visual enhancement) -->
    <span class="absolute inset-0 flex items-center justify-between px-1.5 pointer-events-none">
      <span class="text-yellow-400 opacity-70 flex items-center justify-center w-4 h-4">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
      </span>
      <span class="text-gray-500 dark:text-gray-400 opacity-70 flex items-center justify-center w-4 h-4">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
      </span>
    </span>
  </button>
</template>

<style scoped>
.theme-toggle {
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.toggle-knob {
  top: 0.25rem;
  left: 0;
  transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.5); /* Spring animation effect */
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.7); }
  to { opacity: 1; transform: scale(1); }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-in-out;
}

.dark .theme-toggle {
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);
}

/* Custom spring animation timing function */
.ease-spring {
  transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.5);
}
</style>
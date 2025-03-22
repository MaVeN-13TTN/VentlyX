<script setup lang="ts">
defineProps({
  title: {
    type: String,
    default: 'Something went wrong'
  },
  message: {
    type: String,
    default: 'We encountered an error while processing your request.'
  },
  actionLabel: {
    type: String,
    default: 'Try Again'
  },
  showAction: {
    type: Boolean,
    default: true
  },
  icon: {
    type: String,
    default: 'error' // Options: 'error', 'warning', 'info'
  }
});

defineEmits(['retry']);
</script>

<template>
  <div class="flex flex-col items-center justify-center p-8 bg-white dark:bg-background-dark/50 rounded-xl shadow-md">
    <!-- Icon based on type -->
    <div class="mb-6">
      <!-- Error icon -->
      <div v-if="icon === 'error'" class="w-20 h-20 flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      
      <!-- Warning icon -->
      <div v-else-if="icon === 'warning'" class="w-20 h-20 flex items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      
      <!-- Info icon -->
      <div v-else-if="icon === 'info'" class="w-20 h-20 flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
    </div>
    
    <!-- Error title -->
    <h2 class="text-xl font-semibold mb-2 text-center text-text-light dark:text-text-dark">{{ title }}</h2>
    
    <!-- Error message -->
    <p class="text-gray-600 dark:text-gray-400 mb-6 text-center max-w-lg">{{ message }}</p>
    
    <!-- Action button -->
    <button 
      v-if="showAction" 
      @click="$emit('retry')" 
      class="flex items-center bg-primary hover:bg-primary-600 dark:bg-dark-primary dark:hover:bg-dark-primary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      {{ actionLabel }}
    </button>
  </div>
</template>
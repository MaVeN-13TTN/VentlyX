<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value: string) => ['sm', 'md', 'lg'].includes(value)
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value: string) => ['primary', 'secondary', 'white'].includes(value)
  },
  fullScreen: {
    type: Boolean,
    default: false
  },
  text: {
    type: String,
    default: 'Loading...'
  }
});

// Computed class for size
const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm': return 'w-4 h-4';
    case 'lg': return 'w-10 h-10';
    default: return 'w-6 h-6';
  }
});

// Computed class for color
const colorClass = computed(() => {
  switch (props.color) {
    case 'secondary': return 'text-secondary dark:text-dark-secondary';
    case 'white': return 'text-white';
    default: return 'text-primary dark:text-dark-primary';
  }
});
</script>

<template>
  <div :class="[
    'flex items-center justify-center', 
    fullScreen ? 'fixed inset-0 z-50 bg-white/80 dark:bg-background-dark/80 backdrop-blur-sm' : ''
  ]">
    <div class="flex flex-col items-center">
      <svg
        :class="[sizeClass, colorClass, 'animate-spin']"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        aria-hidden="true"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        ></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
      <span v-if="text" class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ text }}</span>
    </div>
  </div>
</template>
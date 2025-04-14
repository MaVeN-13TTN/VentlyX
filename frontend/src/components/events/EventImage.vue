<template>
  <div class="relative w-full h-full">
    <!-- Placeholder -->
    <div
      v-if="!isLoaded"
      class="absolute inset-0 bg-gray-200 dark:bg-gray-700 animate-pulse"
    >
      <div class="w-full h-full flex items-center justify-center">
        <svg
          class="w-8 h-8 text-gray-400 dark:text-gray-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
      </div>
    </div>

    <!-- Image -->
    <img
      :src="src"
      :alt="alt"
      :class="[
        'w-full h-full object-cover transition-opacity duration-300',
        isLoaded ? 'opacity-100' : 'opacity-0'
      ]"
      loading="lazy"
      @load="handleLoad"
      @error="handleError"
    />

    <!-- Error State -->
    <div
      v-if="hasError"
      class="absolute inset-0 bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
    >
      <div class="text-center">
        <svg
          class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
        <p class="text-sm text-gray-500 dark:text-gray-400">Failed to load image</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  src: string;
  alt: string;
  aspectRatio?: string;
}

const props = defineProps<Props>();
const isLoaded = ref(false);
const hasError = ref(false);

const handleLoad = () => {
  isLoaded.value = true;
};

const handleError = () => {
  hasError.value = true;
  isLoaded.value = true;
};
</script> 
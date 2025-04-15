<template>
  <div class="relative max-w-lg mx-auto mb-12">
    <div class="relative flex items-center">
      <input
        type="text"
        :placeholder="placeholder"
        class="w-full px-5 py-3 pr-14 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500/50 dark:focus:ring-primary-400/50 shadow-sm text-gray-800 dark:text-white"
        v-model="searchQuery"
        @input="handleInput"
        @keydown.enter="handleSearch"
      />
      <button
        @click="handleSearch"
        class="absolute right-5 top-1/2 -translate-y-1/2 text-primary-500 dark:text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 z-10"
        aria-label="Search"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </button>
      <button
        v-if="searchQuery"
        @click="clearSearch"
        class="absolute right-14 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 z-10"
        aria-label="Clear search"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onUnmounted, watch } from 'vue';

const props = defineProps({
  initialQuery: {
    type: String,
    default: ''
  },
  debounceTime: {
    type: Number,
    default: 300
  },
  placeholder: {
    type: String,
    default: 'Search for events...'
  }
});

const emit = defineEmits(['search']);

const searchQuery = ref(props.initialQuery);
let debounceTimeout: number | null = null;

const handleInput = () => {
  if (debounceTimeout) {
    clearTimeout(debounceTimeout);
  }

  debounceTimeout = window.setTimeout(() => {
    emit('search', searchQuery.value);
  }, props.debounceTime);
};

const handleSearch = () => {
  emit('search', searchQuery.value);
};

const clearSearch = () => {
  searchQuery.value = '';
  emit('search', '');
};

// Watch for prop changes to stay in sync
watch(() => props.initialQuery, (newVal) => {
  if (newVal !== searchQuery.value) {
    searchQuery.value = newVal;
  }
});

onUnmounted(() => {
  if (debounceTimeout) {
    clearTimeout(debounceTimeout);
  }
});
</script>
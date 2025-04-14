<template>
  <div class="flex items-center space-x-2">
    <button
      @click="toggleView('grid')"
      class="p-2 rounded-lg transition-colors duration-200"
      :class="[
        view === 'grid'
          ? 'bg-primary-500 dark:bg-primary-400 text-white'
          : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
      ]"
      aria-label="Grid view"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
      </svg>
    </button>
    <button
      @click="toggleView('list')"
      class="p-2 rounded-lg transition-colors duration-200"
      :class="[
        view === 'list'
          ? 'bg-primary-500 dark:bg-primary-400 text-white'
          : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
      ]"
      aria-label="List view"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
  initialView?: 'grid' | 'list';
}>();

const emit = defineEmits<{
  (e: 'view-change', view: 'grid' | 'list'): void;
}>();

const view = ref<'grid' | 'list'>(props.initialView || 'grid');

const toggleView = (newView: 'grid' | 'list') => {
  if (view.value !== newView) {
    view.value = newView;
    emit('view-change', newView);
  }
};

// Watch for prop changes to stay in sync
watch(() => props.initialView, (newVal) => {
  if (newVal && newVal !== view.value) {
    view.value = newVal;
  }
});
</script> 
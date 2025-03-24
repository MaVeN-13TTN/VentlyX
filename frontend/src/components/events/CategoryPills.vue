<template>
  <div class="mb-8 overflow-x-auto no-scrollbar">
    <div class="flex space-x-3">
      <button
        class="flex-shrink-0 px-5 py-2 rounded-full font-medium transition-all duration-200 text-sm"
        :class="activeCategory === '' ? 'bg-primary-500 dark:bg-primary-400 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'"
        @click="selectCategory('')"
      >
        All
      </button>
      <!-- Category filter buttons -->
      <button
        v-for="category in categories"
        :key="category"
        class="flex-shrink-0 px-5 py-2 rounded-full font-medium transition-all duration-200 text-sm"
        :class="activeCategory === category ? 'bg-primary-500 dark:bg-primary-400 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'"
        @click="selectCategory(category)"
      >
        {{ category }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
  categories: string[];
  initialCategory?: string;
}>();

const emit = defineEmits(['category-change', 'scroll-to-results']);

const activeCategory = ref(props.initialCategory || '');

const selectCategory = (category: string) => {
  activeCategory.value = category;
  emit('category-change', category);
  // Emit scroll event after a short delay to allow for results to update
  setTimeout(() => {
    emit('scroll-to-results');
  }, 100);
};

// Watch for prop changes to stay in sync
watch(() => props.initialCategory, (newVal) => {
  if (newVal !== undefined && newVal !== activeCategory.value) {
    activeCategory.value = newVal;
  }
});
</script>

<style scoped>
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
<template>
  <div class="flex flex-wrap gap-2 mb-6">
    <button
      v-for="category in categories"
      :key="category"
      :class="[
        'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200',
        selectedCategory === category
          ? 'bg-primary text-white dark:bg-dark-primary shadow-md'
          : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
      ]"
      @click="selectCategory(category)"
    >
      {{ category }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const categories = ref<string[]>([]);
const selectedCategory = ref<string | null>(null);

const emit = defineEmits(['scroll-to-results']);

const fetchCategories = async () => {
  try {
    const response = await fetch('/api/v1/events?get_categories=true');
    const data = await response.json();
    categories.value = data.categories;
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  }
};

const selectCategory = async (category: string) => {
  if (selectedCategory.value === category) {
    selectedCategory.value = null;
    await router.push({ path: '/events' });
  } else {
    selectedCategory.value = category;
    await router.push({ 
      path: '/events',
      query: { category }
    });
  }
  
  // Wait for the next tick to ensure the DOM has updated
  await nextTick();
  // Emit scroll event
  emit('scroll-to-results');
};

onMounted(() => {
  // Get the category from URL query params if it exists
  const { category } = router.currentRoute.value.query;
  if (category && typeof category === 'string') {
    selectedCategory.value = category;
  }
  
  fetchCategories();
});
</script>
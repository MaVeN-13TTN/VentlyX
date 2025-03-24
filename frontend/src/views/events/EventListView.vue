<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-2xl font-bold mb-6">Events</h1>
      <p class="text-gray-600 dark:text-gray-400 mb-4">Discover upcoming events.</p>
      
      <!-- Category Filter with scroll handler -->
      <CategoryPills 
        :categories="categories"
        :initial-category="selectedCategory"
        @category-change="handleCategoryChange"
        @scroll-to-results="scrollToResults" 
      />
      
      <!-- All Events Section -->
      <div v-if="!selectedCategory" class="mt-12">
        <h2 class="text-xl font-semibold mb-6 text-text-light dark:text-text-dark">All Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Event cards -->
          <div v-for="event in events" :key="event.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <img v-if="event.image_url" :src="event.image_url" :alt="event.title" class="w-full h-48 object-cover">
            <div class="p-6">
              <h3 class="text-lg font-semibold mb-2">{{ event.title }}</h3>
              <p class="text-gray-600 dark:text-gray-300 mb-4">{{ event.description }}</p>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  {{ new Date(event.start_time).toLocaleDateString() }}
                </span>
                <router-link 
                  :to="{ name: 'event-details', params: { id: event.id }}"
                  class="text-primary dark:text-dark-primary hover:underline"
                >
                  View Details
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtered Events Section -->
      <div v-else ref="resultsSection" class="mt-12">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-text-light dark:text-text-dark">
            {{ selectedCategory }} Events
          </h2>
          <button 
            @click="clearCategory" 
            class="text-sm text-primary dark:text-dark-primary hover:underline flex items-center space-x-1"
          >
            <span>Show all events</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
          <p class="text-red-600 dark:text-red-400">{{ error }}</p>
        </div>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Loading skeleton -->
          <div v-for="i in 6" :key="i" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 animate-pulse">
            <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-lg mb-4"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
          </div>
        </div>

        <div v-else-if="events.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Event cards -->
          <div v-for="event in events" :key="event.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <img v-if="event.image_url" :src="event.image_url" :alt="event.title" class="w-full h-48 object-cover">
            <div class="p-6">
              <h3 class="text-lg font-semibold mb-2">{{ event.title }}</h3>
              <p class="text-gray-600 dark:text-gray-300 mb-4">{{ event.description }}</p>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  {{ new Date(event.start_time).toLocaleDateString() }}
                </span>
                <router-link 
                  :to="{ name: 'event-details', params: { id: event.id }}"
                  class="text-primary dark:text-dark-primary hover:underline"
                >
                  View Details
                </router-link>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <p class="text-gray-500 dark:text-gray-400">No events found in this category.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CategoryPills from '@/components/events/CategoryPills.vue';
import EventService from '@/services/api/EventService';

interface Event {
  id: number;
  title: string;
  description: string;
  start_time: string;
  image_url?: string;
  category?: string;
}

const route = useRoute();
const router = useRouter();
const events = ref<Event[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const resultsSection = ref<HTMLElement | null>(null);
const categories = ref<string[]>([]);
const selectedCategory = ref<string | null>(null);

const handleCategoryChange = async (category: string) => {
  loading.value = true;
  error.value = null;
  
  // Update URL with the new category
  if (category) {
    await router.push({ query: { ...route.query, category } });
  } else {
    const query = { ...route.query };
    delete query.category;
    await router.push({ query });
  }
  selectedCategory.value = category;
  
  try {
    // Use EventService to fetch filtered events
    const response = await EventService.getEvents({ category });
    events.value = response.data.data;
    
    // Scroll to filtered results section after data is loaded
    await nextTick();
    scrollToResults();
  } catch (err: unknown) {
    console.error('Failed to fetch filtered events:', err);
    error.value = 'Failed to load filtered events. Please try again later.';
  } finally {
    loading.value = false;
  }
};

const clearCategory = async () => {
  loading.value = true;
  error.value = null;
  
  selectedCategory.value = null;
  const query = { ...route.query };
  delete query.category;
  await router.push({ query });
  
  try {
    // Use EventService to fetch all events
    const response = await EventService.getEvents();
    events.value = response.data.data;
  } catch (err: unknown) {
    console.error('Failed to fetch events:', err);
    error.value = 'Failed to load events. Please try again later.';
  } finally {
    loading.value = false;
  }
};

const scrollToResults = () => {
  if (resultsSection.value) {
    resultsSection.value.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
      inline: 'nearest'
    });
  }
};

const fetchCategories = async () => {
  try {
    const response = await EventService.getCategories();
    categories.value = response.data.categories;
  } catch (err: unknown) {
    console.error('Failed to fetch categories:', err);
    error.value = 'Failed to load categories. Please try again later.';
  }
};

const fetchEvents = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await EventService.getEvents();
    events.value = response.data.data;
  } catch (err: unknown) {
    console.error('Failed to fetch events:', err);
    error.value = 'Failed to load events. Please try again later.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  // Get the category from URL query params if it exists
  const { category } = route.query;
  if (category && typeof category === 'string') {
    selectedCategory.value = category;
  }
  
  Promise.all([
    fetchCategories(),
    fetchEvents()
  ]);
});

// Watch for route query changes to update selected category
watch(() => route.query, () => {
  const { category } = route.query;
  if (category && typeof category === 'string') {
    selectedCategory.value = category;
  } else {
    selectedCategory.value = null;
  }
}, { deep: true });
</script>
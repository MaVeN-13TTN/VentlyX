<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-2xl font-bold mb-6">Events</h1>
      <p class="text-gray-600 dark:text-gray-400 mb-4">Discover upcoming events.</p>
      
      <!-- Category Filter with scroll handler -->
      <CategoryFilter @scroll-to-results="scrollToResults" />
      
      <!-- Results section with ref for scrolling -->
      <div ref="resultsSection">
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
          <p class="text-gray-500 dark:text-gray-400">No events available.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import CategoryFilter from '@/components/events/CategoryFilter.vue';

interface Event {
  id: number;
  title: string;
  description: string;
  start_time: string;
  image_url?: string;
}

const route = useRoute();
const events = ref<Event[]>([]);
const loading = ref(true);
const resultsSection = ref<HTMLElement | null>(null);

const fetchEvents = async () => {
  loading.value = true;
  try {
    const queryParams = new URLSearchParams(route.query as Record<string, string>);
    const response = await fetch(`/api/v1/events?${queryParams}`);
    const data = await response.json();
    events.value = data.data;
    // Wait for the DOM to update with new results
    await nextTick();
    // If we have a category selected, scroll to results
    if (route.query.category) {
      scrollToResults();
    }
  } catch (error) {
    console.error('Failed to fetch events:', error);
  } finally {
    loading.value = false;
  }
};

const scrollToResults = () => {
  if (resultsSection.value) {
    // Add a small delay to ensure content is rendered
    setTimeout(() => {
      resultsSection.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
        inline: 'nearest'
      });
    }, 100);
  }
};

onMounted(() => {
  fetchEvents();
});

// Watch for route query changes to refetch events
watch(() => route.query, () => {
  fetchEvents();
}, { deep: true });
</script>
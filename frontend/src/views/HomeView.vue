<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Hero Section -->
    <div class="relative overflow-hidden min-h-screen flex items-center rounded-3xl pt-16">
      <!-- Hero background with animated gradient -->
      <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-secondary/20 dark:from-primary/10 dark:to-secondary/10 rounded-b-3xl"></div>
      <div class="absolute inset-0 opacity-20">
        <div class="absolute -inset-[10%] top-1/3 left-2/3 w-96 h-96 bg-accent-pink rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
        <div class="absolute -inset-[10%] top-1/4 right-2/3 w-96 h-96 bg-secondary rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
        <div class="absolute -inset-[10%] bottom-1/3 left-1/3 w-96 h-96 bg-accent-blue rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
      </div>

      <div class="container mx-auto relative center-content w-full mt-10">
        <div class="text-center max-w-3xl mx-auto p-6 bg-white/5 dark:bg-background-dark/5 backdrop-blur-sm rounded-2xl border border-white/10 dark:border-gray-800/10 shadow-xl">
          <h1 class="text-5xl lg:text-6xl font-bold mb-6 text-text-light dark:text-text-dark leading-tight">
            Find and Book<br/><span class="bg-gradient-to-r from-primary to-accent-pink bg-clip-text text-transparent">Amazing Events</span>
          </h1>
          <p class="text-xl mb-8 text-text-light/80 dark:text-text-dark/80 max-w-2xl">
            Discover events that match your interests, book tickets, and create unforgettable memories with VentlyX.
          </p>
          <div class="flex flex-wrap gap-4 justify-center">
            <router-link
              to="/events"
              class="group bg-gradient-to-r from-primary to-secondary text-white px-8 py-4 rounded-full font-medium hover:shadow-lg transition-all duration-300 flex items-center space-x-2"
            >
              <span>Browse Events</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </router-link>
            <router-link
              to="/register"
              class="bg-transparent border-2 border-primary text-primary dark:text-dark-primary dark:border-dark-primary px-8 py-4 rounded-full font-medium hover:bg-primary/5 dark:hover:bg-dark-primary/5 transition-all duration-300"
            >
              Create Account
            </router-link>
          </div>
        </div>

        <!-- Abstract decorative element -->
        <div class="hidden lg:block absolute right-10 top-1/2 -translate-y-1/2">
          <div class="relative w-80 h-80">
            <div class="absolute inset-0 rounded-full bg-gradient-to-br from-secondary/30 to-accent-pink/30 animate-pulse"></div>
            <div class="absolute inset-4 rounded-full bg-gradient-to-tr from-primary/30 to-accent-blue/30 animate-pulse animation-delay-1000"></div>
            <div class="absolute inset-8 rounded-full bg-white dark:bg-background-dark/80 flex items-center justify-center shadow-2xl">
              <div class="text-5xl font-bold bg-gradient-to-r from-primary to-accent-pink bg-clip-text text-transparent">
                VX
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container mx-auto py-12">
      <!-- Featured Events Section -->
      <div class="mt-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-12">
          <h2 class="text-2xl font-bold text-text-light dark:text-text-dark mb-4 md:mb-0">
            <span class="border-b-4 border-primary dark:border-dark-primary pb-2">Featured Events</span>
          </h2>
          <router-link to="/events" class="text-primary dark:text-dark-primary hover:underline flex items-center space-x-1 font-medium">
            <span>View all events</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </router-link>
        </div>

        <div v-if="loadingFeatured" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <SkeletonLoader v-for="i in 6" :key="i" type="card" :animated="true" />
        </div>

        <div v-else-if="featuredEvents.length === 0" class="text-center py-12 bg-white dark:bg-background-dark/50 rounded-2xl shadow-md">
          <p class="text-text-light/70 dark:text-text-dark/70 mb-4">No featured events found</p>
        </div>
        <div v-else class="relative overflow-hidden rounded-2xl">
          <!-- Carousel Track -->
          <div
            class="flex transition-transform duration-500 ease-in-out"
            :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
            @mouseenter="pauseAutoPlay"
            @mouseleave="resumeAutoPlay"
          >
            <div
              v-for="(event, index) in featuredEvents"
              :key="event.id"
              class="w-full flex-shrink-0 px-4"
            >
              <EventCard
                :event="event"
                :color-index="index"
                class="h-full"
              />
            </div>
          </div>
          <!-- Navigation Arrows -->
          <button
            @click="prevSlide"
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 p-2 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-800 z-10 transform -translate-x-2 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"
            aria-label="Previous slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary dark:text-dark-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            @click="nextSlide"
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 p-2 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-800 z-10 transform translate-x-2 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"
            aria-label="Next slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary dark:text-dark-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <!-- Dots Navigation -->
          <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
            <button
              v-for="(_, index) in featuredEvents"
              :key="index"
              @click="goToSlide(index)"
              class="w-3 h-3 rounded-full transition-all duration-200 border border-white/50 dark:border-gray-700/50"
              :class="[
                currentIndex === index
                  ? 'bg-primary dark:bg-dark-primary scale-110'
                  : 'bg-gray-300/70 dark:bg-gray-600/70 hover:bg-gray-400 dark:hover:bg-gray-500'
              ]"
              :aria-label="`Go to slide ${index + 1}`"
            ></button>
          </div>
          <!-- Auto-play indicator -->
          <div class="absolute top-4 right-4 z-10">
            <button
              @click="toggleAutoPlay"
              class="p-2 rounded-full bg-white/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 transition-colors"
              :aria-label="autoPlayInterval ? 'Pause slideshow' : 'Play slideshow'"
            >
              <svg v-if="autoPlayInterval" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>
          <!-- Progress bar -->
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200/50 dark:bg-gray-700/50 z-10">
            <div
              class="h-full bg-primary dark:bg-dark-primary transition-all duration-300 ease-linear"
              :style="{ width: `${slideProgress}%` }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Upcoming Events Section -->
      <div class="mt-16">
        <div class="flex flex-col md:flex-row justify-between items-center mb-12">
          <h2 class="text-2xl font-bold text-text-light dark:text-text-dark mb-4 md:mb-0">
            <span class="border-b-4 border-primary dark:border-dark-primary pb-2">Upcoming Events</span>
          </h2>
          <router-link to="/events" class="text-primary dark:text-dark-primary hover:underline flex items-center space-x-1 font-medium">
            <span>View all events</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </router-link>
        </div>

        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <SkeletonLoader v-for="i in 6" :key="i" type="card" :animated="true" />
        </div>

        <div v-else-if="upcomingEvents.length === 0" class="text-center py-12 bg-white dark:bg-background-dark/50 rounded-2xl shadow-md">
          <p class="text-text-light/70 dark:text-text-dark/70 mb-4">No upcoming events found</p>
        </div>

        <div v-else>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <EventCard
              v-for="(event, index) in events"
              :key="event.id"
              :event="event"
              :color-index="index"
            />
          </div>

          <!-- Load more trigger -->
          <div ref="loadMoreTrigger" class="mt-8 text-center">
            <div v-if="loadingMore" class="py-4">
              <div class="inline-block animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-primary dark:border-dark-primary"></div>
              <span class="ml-2 text-text-light/70 dark:text-text-dark/70">Loading more events...</span>
            </div>
            <div v-else-if="hasMorePages" class="py-4 text-text-light/70 dark:text-text-dark/70">
              Scroll for more events
            </div>
            <div v-else class="py-4 text-text-light/70 dark:text-text-dark/70">
              No more events to load
            </div>
          </div>
        </div>
      </div>

      <!-- Category Showcases -->
      <div v-for="category in categorizedEvents" :key="category.category" class="mt-16">
        <div class="flex flex-col md:flex-row justify-between items-center mb-12">
          <h2 class="text-2xl font-bold text-text-light dark:text-text-dark mb-4 md:mb-0">
            <span class="border-b-4 border-primary dark:border-dark-primary pb-2">{{ category.category }} Events</span>
          </h2>
          <router-link :to="{ path: '/events', query: { category: category.category }}" class="text-primary dark:text-dark-primary hover:underline flex items-center space-x-1 font-medium">
            <span>View all {{ category.category }} events</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </router-link>
        </div>

        <div v-if="loadingCategorized" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <SkeletonLoader v-for="i in 3" :key="i" type="card" :animated="true" />
        </div>

        <div v-else-if="category.events.length === 0" class="text-center py-12 bg-white dark:bg-background-dark/50 rounded-2xl shadow-md">
          <p class="text-text-light/70 dark:text-text-dark/70 mb-4">No events found in this category</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <EventCard
            v-for="(event, index) in category.events.slice(0, 3)"
            :key="event.id"
            :event="event"
            :color-index="index"
          />
        </div>
      </div>
    </div>

    <!-- How It Works Section -->
    <div class="bg-white dark:bg-background-dark/50 py-24 rounded-t-3xl">
      <div class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold mb-16 text-center text-text-light dark:text-text-dark">
          <span class="relative">
            How It Works
            <span class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-gradient-to-r from-primary to-accent-pink"></span>
          </span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
          <!-- Step 1 -->
          <div class="relative text-center group">
            <!-- Connector line between steps (hidden on mobile) -->
            <div class="hidden md:block absolute top-10 left-full w-full h-0.5 bg-gradient-to-r from-primary/50 to-transparent -z-10 transform translate-x-4"></div>

            <div class="relative inline-flex mb-8">
              <!-- Icon background with animated border -->
              <div class="absolute inset-0 rounded-full bg-gradient-to-r from-primary to-secondary animate-spin-slow opacity-70"></div>
              <div class="relative bg-white dark:bg-background-dark w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold z-10 group-hover:scale-110 transition-transform duration-300">
                <span class="bg-gradient-to-r from-primary to-accent-pink bg-clip-text text-transparent">1</span>
              </div>
            </div>

            <h3 class="text-xl font-semibold mb-3 text-text-light dark:text-text-dark">Find Events</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 max-w-xs mx-auto">Browse our curated list of events or search for specific categories that match your interests.</p>
          </div>

          <!-- Step 2 -->
          <div class="relative text-center group">
            <!-- Connector line between steps (hidden on mobile) -->
            <div class="hidden md:block absolute top-10 left-full w-full h-0.5 bg-gradient-to-r from-primary/50 to-transparent -z-10 transform translate-x-4"></div>

            <div class="relative inline-flex mb-8">
              <!-- Icon background with animated border -->
              <div class="absolute inset-0 rounded-full bg-gradient-to-r from-secondary to-accent-blue animate-spin-slow opacity-70"></div>
              <div class="relative bg-white dark:bg-background-dark w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold z-10 group-hover:scale-110 transition-transform duration-300">
                <span class="bg-gradient-to-r from-secondary to-accent-blue bg-clip-text text-transparent">2</span>
              </div>
            </div>

            <h3 class="text-xl font-semibold mb-3 text-text-light dark:text-text-dark">Book Tickets</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 max-w-xs mx-auto">Select your preferred tickets and complete the secure payment process in just a few clicks.</p>
          </div>

          <!-- Step 3 -->
          <div class="relative text-center group">
            <div class="relative inline-flex mb-8">
              <!-- Icon background with animated border -->
              <div class="absolute inset-0 rounded-full bg-gradient-to-r from-accent-pink to-secondary animate-spin-slow opacity-70"></div>
              <div class="relative bg-white dark:bg-background-dark w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold z-10 group-hover:scale-110 transition-transform duration-300">
                <span class="bg-gradient-to-r from-accent-pink to-secondary bg-clip-text text-transparent">3</span>
              </div>
            </div>

            <h3 class="text-xl font-semibold mb-3 text-text-light dark:text-text-dark">Attend & Enjoy</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 max-w-xs mx-auto">Receive your tickets via email, scan the QR code at the venue, and enjoy an amazing experience.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="py-20 relative overflow-hidden rounded-b-3xl">
      <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-accent-pink/20 dark:from-primary/10 dark:to-accent-pink/10 rounded-b-3xl"></div>
      <div class="container mx-auto px-4 py-8 relative">
        <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-3xl font-bold mb-6 text-text-light dark:text-text-dark">Ready to Discover Amazing Events?</h2>
          <p class="text-xl mb-10 text-text-light/80 dark:text-text-dark/80">Join thousands of people who are already using VentlyX to find and attend the best events near them.</p>
          <router-link
            to="/register"
            class="inline-flex items-center bg-gradient-to-r from-primary to-accent-pink text-white px-8 py-4 rounded-full font-medium hover:shadow-lg shadow-primary/20 transition-all duration-300"
          >
            <span>Get Started Today</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import EventCard from '@/components/events/EventCard.vue';
import HorizontalEventScroller from '@/components/events/HorizontalEventScroller.vue';
import SkeletonLoader from '@/components/common/SkeletonLoader.vue';
import EventService from '@/services/api/EventService';

// Get the EventService instance
const eventService = EventService.getInstance();

// Define EventData interface to match the Event interface from EventService
interface EventData {
  id: number;
  title: string;
  description: string;
  start_time: string;
  image_url?: string;
  category?: string;
  price?: number;
  location?: string;
  featured?: boolean;
  min_price?: number;
}

// State for events
const events = ref<EventData[]>([]);
const featuredEvents = ref<EventData[]>([]);
const categorizedEvents = ref<{category: string, events: EventData[]}[]>([]);
const categories = ref<string[]>([]);

// Loading states
const isLoading = ref(true);
const loadingFeatured = ref(true);
const loadingCategorized = ref(true);
const loadingMore = ref(false);

// Lazy loading
const loadMoreTrigger = ref<HTMLElement | null>(null);

// Pagination
const currentPage = ref(1);
const totalPages = ref(1);
const hasMorePages = computed(() => currentPage.value < totalPages.value);

// Filters
const searchQuery = ref('');
const selectedCategory = ref('');

// Popular categories to feature on the homepage
const popularCategories = ['Music', 'Conference', 'Workshop', 'Sports'];

// Get the current date for "upcoming" filter
const today = new Date().toISOString();

// Create an extended version of the EventFilters interface that includes our additional filter properties
interface ExtendedEventFilters {
  category?: string | null;
  dateRange?: [Date, Date] | null;
  location?: string;
  selectedCategories?: string[];
  priceRange?: [number, number];
  page?: number;
  per_page?: number;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
  featured?: boolean;
  start_time_after?: string;
  search?: string;
}

// Apply current filters to events
const applyFilters = () => {
  if (!events.value.length) return;

  // If no search or category filter, show all events
  if (!searchQuery.value && !selectedCategory.value) {
    return;
  }

  let filteredEvents = [...events.value];

  // Apply search filter if present
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filteredEvents = filteredEvents.filter(event =>
      event.title.toLowerCase().includes(query) ||
      event.description.toLowerCase().includes(query) ||
      (event.location?.toLowerCase().includes(query) || false) ||
      (event.category?.toLowerCase().includes(query) || false)
    );
  }

  // Apply category filter if present
  if (selectedCategory.value) {
    // Handle special filters
    if (selectedCategory.value === 'featured') {
      filteredEvents = filteredEvents.filter(event => event.featured);
    } else if (selectedCategory.value === 'upcoming') {
      filteredEvents = filteredEvents.filter(event =>
        new Date(event.start_time) > new Date(today)
      ).sort((a, b) =>
        new Date(a.start_time).getTime() - new Date(b.start_time).getTime()
      );
    } else {
      // Regular category filter
      filteredEvents = filteredEvents.filter(event =>
        event.category === selectedCategory.value
      );
    }
  }

  events.value = filteredEvents;
};

// Fetch all necessary data on mount
onMounted(async () => {
  try {
    await Promise.all([
      fetchCategories(),
      fetchFeaturedEvents(),
      fetchAllEvents()
    ]);

    // Start carousel autoplay
    startAutoPlay();

    // Pause autoplay when tab is not visible
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopAutoPlay();
      } else {
        startAutoPlay();
      }
    });

    // Fetch events by category after we know what categories exist
    if (categories.value.length > 0) {
      await fetchEventsByCategories();
    }

    // Setup intersection observer for lazy loading
    setupIntersectionObserver();
  } catch (error) {
    console.error('Error loading initial data:', error);
  }
});

// Setup intersection observer for lazy loading
const setupIntersectionObserver = () => {
  if (!loadMoreTrigger.value) return;

  const observer = new IntersectionObserver((entries) => {
    const [entry] = entries;
    if (entry.isIntersecting && !isLoading.value && !loadingMore.value && hasMorePages.value) {
      loadMore();
    }
  }, {
    root: null,
    threshold: 0.1,
    rootMargin: '0px 0px 200px 0px' // Load more when trigger is 200px from viewport
  });

  observer.observe(loadMoreTrigger.value);
};

onUnmounted(() => {
  stopAutoPlay();
  stopSlideProgress();
  document.removeEventListener('visibilitychange', () => {});
});

// Watch for filter changes
watch([searchQuery, selectedCategory], () => {
  currentPage.value = 1;
  applyFilters();
});

// Fetch available categories
const fetchCategories = async () => {
  try {
    const response = await eventService.getCategories();
    if (!response.data.categories || response.data.categories.length === 0) {
      categories.value = popularCategories;
    } else {
      categories.value = response.data.categories || [];
    }
  } catch (error) {
    console.error('Error fetching categories:', error);
    categories.value = popularCategories;
  }
};

// Fetch featured events
const fetchFeaturedEvents = async () => {
  loadingFeatured.value = true;
  try {
    const response = await eventService.getEvents({ featured: true } as ExtendedEventFilters);
    featuredEvents.value = response.data.data || [];
  } catch (error) {
    console.error('Error fetching featured events:', error);
    featuredEvents.value = [];
  } finally {
    loadingFeatured.value = false;
  }
};

// Fetch all events for filtering with lazy loading
const fetchAllEvents = async () => {
  isLoading.value = true;
  try {
    const response = await eventService.getEvents({ per_page: 6, page: currentPage.value });

    // If it's the first page, replace the events array
    // Otherwise, append to the existing array for infinite scrolling
    if (currentPage.value === 1) {
      events.value = response.data.data || [];
    } else {
      events.value = [...events.value, ...(response.data.data || [])];
    }

    totalPages.value = Math.ceil(response.data.meta.total / response.data.meta.per_page);
  } catch (error) {
    console.error('Error fetching all events:', error);
    if (currentPage.value === 1) {
      events.value = [];
    }
    totalPages.value = 1;
  } finally {
    isLoading.value = false;
  }
};

// Fetch events by category for horizontal scrollers
const fetchEventsByCategories = async () => {
  loadingCategorized.value = true;
  try {
    // Get events for a few predefined categories
    const categoriesToFetch = popularCategories.filter(cat =>
      categories.value.includes(cat)
    ).slice(0, 3); // Limit to 3 categories for performance

    if (categoriesToFetch.length === 0) {
      categorizedEvents.value = [];
    } else {
      const categoryPromises = categoriesToFetch.map(async (category) => {
        const response = await eventService.getEvents({ category });
        return {
          category,
          events: response.data.data || []
        };
      });

      categorizedEvents.value = await Promise.all(categoryPromises);
    }
  } catch (error) {
    console.error('Error fetching categorized events:', error);
    categorizedEvents.value = [];
  } finally {
    loadingCategorized.value = false;
  }
};

// Load more events
const loadMore = async () => {
  if (loadingMore.value || !hasMorePages.value) return;

  loadingMore.value = true;

  try {
    currentPage.value += 1;
    const filters = {
      page: currentPage.value,
      per_page: 9,
      search: searchQuery.value || undefined,
      category: selectedCategory.value === 'featured' || selectedCategory.value === 'upcoming' ? undefined : selectedCategory.value
    };

    let response;
    if (selectedCategory.value === 'featured') {
      response = await eventService.getEvents({ featured: true } as ExtendedEventFilters);
    } else if (selectedCategory.value === 'upcoming') {
      response = await eventService.getEvents({
        start_time_after: new Date().toISOString()
      } as ExtendedEventFilters);
    } else {
      response = await eventService.getEvents(filters);
    }

    const additionalEvents = response.data.data || [];
    events.value = [...events.value, ...additionalEvents];
  } catch (error) {
    console.error('Error loading more events:', error);
    currentPage.value -= 1; // Revert page increment on error
  } finally {
    loadingMore.value = false;
  }
};

// Handle search
const searchResultsSection = ref<HTMLElement | null>(null);

const handleSearch = async (query: string) => {
  searchQuery.value = query;

  // Wait for the DOM to update after the search results are filtered
  await nextTick();

  // Scroll to the search results section with smooth behavior
  if (searchResultsSection.value) {
    const yOffset = -100; // Offset to account for fixed header
    const y = searchResultsSection.value.getBoundingClientRect().top + window.pageYOffset + yOffset;

    window.scrollTo({
      top: y,
      behavior: 'smooth'
    });

    // Add a brief highlight effect to the section
    searchResultsSection.value.classList.add('search-highlight');
    setTimeout(() => {
      searchResultsSection.value?.classList.remove('search-highlight');
    }, 1000);
  }
};

// Handle category selection
const handleCategoryChange = (category: string) => {
  selectedCategory.value = category;
};

// Reset all filters
const resetFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = '';
  currentPage.value = 1;
  events.value = [];
};

// Carousel state
const currentIndex = ref(0);
const autoPlayInterval = ref<number | null>(null);
const slideProgress = ref(0);
const slideTimer = ref<number | null>(null);
const slideDuration = 5000; // 5 seconds per slide

// Carousel methods
const nextSlide = () => {
  if (featuredEvents.value.length <= 1) return;
  currentIndex.value = (currentIndex.value + 1) % featuredEvents.value.length;
  resetSlideProgress();
};

const prevSlide = () => {
  if (featuredEvents.value.length <= 1) return;
  currentIndex.value = currentIndex.value === 0
    ? featuredEvents.value.length - 1
    : currentIndex.value - 1;
  resetSlideProgress();
};

const goToSlide = (index: number) => {
  currentIndex.value = index;
  resetSlideProgress();
};

const startAutoPlay = () => {
  if (featuredEvents.value.length <= 1) return;
  stopAutoPlay(); // Clear any existing interval
  autoPlayInterval.value = window.setInterval(() => {
    nextSlide();
  }, slideDuration); // Change slide every 5 seconds

  // Start progress bar
  startSlideProgress();
};

const stopAutoPlay = () => {
  if (autoPlayInterval.value) {
    clearInterval(autoPlayInterval.value);
    autoPlayInterval.value = null;
  }

  // Stop progress bar
  stopSlideProgress();
};

const pauseAutoPlay = () => {
  stopAutoPlay();
};

const resumeAutoPlay = () => {
  startAutoPlay();
};

const toggleAutoPlay = () => {
  if (autoPlayInterval.value) {
    pauseAutoPlay();
  } else {
    resumeAutoPlay();
  }
};

const startSlideProgress = () => {
  // Reset progress
  slideProgress.value = 0;

  // Clear any existing timer
  if (slideTimer.value) {
    clearInterval(slideTimer.value);
  }

  // Start progress timer
  const startTime = Date.now();
  slideTimer.value = window.setInterval(() => {
    const elapsed = Date.now() - startTime;
    slideProgress.value = Math.min((elapsed / slideDuration) * 100, 100);
  }, 16); // Update roughly every frame (60fps)
};

const stopSlideProgress = () => {
  if (slideTimer.value) {
    clearInterval(slideTimer.value);
    slideTimer.value = null;
  }
  slideProgress.value = 0;
};

const resetSlideProgress = () => {
  if (autoPlayInterval.value) {
    stopSlideProgress();
    startSlideProgress();
  }
};

// Computed properties
const upcomingEvents = computed(() => {
  return events.value
    .filter((event: EventData) => new Date(event.start_time) > new Date())
    .sort((a: EventData, b: EventData) => new Date(a.start_time).getTime() - new Date(b.start_time).getTime());
});
</script>

<style scoped>
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -30px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 10s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

.animate-spin-slow {
  animation: spin 8s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Carousel-specific styles */
.carousel-enter-active,
.carousel-leave-active {
  transition: opacity 0.5s ease;
}

.carousel-enter-from,
.carousel-leave-to {
  opacity: 0;
}

.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 500ms;
}

.search-highlight {
  animation: highlight 1s ease-in-out;
}

@keyframes highlight {
   0%, 20% {
    background-color: rgba(var(--color-primary), 0.1);
    transform: scale(1.01);
  }
  100% {
    background-color: transparent;
    transform: scale(1);
  }
}
</style>
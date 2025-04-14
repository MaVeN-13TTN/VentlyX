<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6">
        <div>
          <h1 class="text-xl sm:text-2xl font-bold text-text-light dark:text-text-dark">Events</h1>
          <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Discover upcoming events.</p>
        </div>
        <EventViewToggle 
          :initial-view="currentView"
          @view-change="handleViewChange"
          class="mt-2 sm:mt-0"
        />
      </div>

      <!-- Event Search -->
      <div class="mb-8">
        <EventSearch 
          :initial-query="searchQuery"
          @search="handleSearchQuery"
          placeholder="Search for events by title, description, or location..."
        />
      </div>
      
      <!-- Mobile Filter Toggle -->
      <div class="sm:hidden mb-4">
        <button
          @click="toggleMobileFilters"
          class="w-full flex items-center justify-between px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm"
        >
          <span class="text-sm font-medium text-text-light dark:text-text-dark">Filters</span>
          <span class="text-sm text-primary dark:text-dark-primary">
            {{ hasActiveFilters ? `${activeFiltersCount} active` : 'None' }}
          </span>
        </button>
      </div>
      
      <!-- Advanced Filters -->
      <div
        v-show="!isMobile || showMobileFilters"
        class="mb-4 sm:mb-6 transition-all duration-300"
      >
        <AdvancedEventFilters
          :categories="categories"
          :initial-filters="currentFilters"
          @apply-filters="handleAdvancedFilters"
        />
      </div>
      
      <!-- Category Filter with scroll handler -->
      <div class="mb-4 sm:mb-6">
        <CategoryPills 
          :categories="categories"
          :initial-category="selectedCategory"
          @category-change="handleCategoryChange"
          @scroll-to-results="scrollToResults" 
        />
      </div>
      
      <!-- All Events Section -->
      <div v-if="!selectedCategory" class="mt-8 sm:mt-12">
        <h2 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 text-text-light dark:text-text-dark">All Events</h2>
        <div :class="[
          currentView === 'grid' 
            ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6'
            : 'space-y-4'
        ]">
          <!-- Event cards -->
          <div 
            v-for="event in events" 
            :key="event.id" 
            :class="[
              'bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden',
              currentView === 'list' ? 'flex flex-col sm:flex-row' : ''
            ]"
          >
            <div :class="[
              'relative',
              currentView === 'list' ? 'sm:w-1/3 h-48 sm:h-full' : 'w-full h-48'
            ]">
              <EventImage
                v-if="event.image_url"
                :src="event.image_url"
                :alt="event.title"
              />
            </div>
            <div :class="[
              'p-4 sm:p-6',
              currentView === 'list' ? 'sm:w-2/3' : 'w-full'
            ]">
              <h3 class="text-base sm:text-lg font-semibold mb-2 text-text-light dark:text-text-dark">{{ event.title }}</h3>
              <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mb-4 line-clamp-2">{{ event.description }}</p>
              <div class="flex items-center justify-between">
                <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                  {{ new Date(event.start_time).toLocaleDateString() }}
                </span>
                <router-link 
                  :to="{ name: 'event-details', params: { id: event.id }}"
                  class="text-sm sm:text-base text-primary dark:text-dark-primary hover:underline"
                >
                  View Details
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtered Events Section -->
      <div v-else ref="resultsSection" class="mt-8 sm:mt-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6">
          <h2 class="text-lg sm:text-xl font-semibold text-text-light dark:text-text-dark mb-2 sm:mb-0">
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

        <div v-if="loading" :class="[
          currentView === 'grid' 
            ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'
            : 'space-y-4'
        ]">
          <!-- Loading skeleton -->
          <div 
            v-for="i in 6" 
            :key="i" 
            :class="[
              'bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 animate-pulse',
              currentView === 'list' ? 'flex' : ''
            ]"
          >
            <div :class="[
              'bg-gray-200 dark:bg-gray-700 rounded-lg',
              currentView === 'list' ? 'w-1/3 h-full' : 'h-48 w-full mb-4'
            ]"></div>
            <div :class="[
              'flex-1',
              currentView === 'list' ? 'ml-6' : ''
            ]">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <div v-else-if="events.length > 0" :class="[
          currentView === 'grid' 
            ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'
            : 'space-y-4'
        ]">
          <!-- Event cards -->
          <div 
            v-for="event in events" 
            :key="event.id" 
            :class="[
              'bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden',
              currentView === 'list' ? 'flex' : ''
            ]"
          >
            <div :class="[
              'relative',
              currentView === 'list' ? 'w-1/3' : 'w-full'
            ]">
              <img 
                v-if="event.image_url" 
                :src="event.image_url" 
                :alt="event.title" 
                :class="[
                  'object-cover',
                  currentView === 'list' ? 'h-full' : 'h-48 w-full'
                ]"
              >
            </div>
            <div :class="[
              'p-6',
              currentView === 'list' ? 'w-2/3' : 'w-full'
            ]">
              <h3 class="text-lg font-semibold mb-2 text-text-light dark:text-text-dark">{{ event.title }}</h3>
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

      <!-- Pagination and Sort Controls -->
      <div v-if="paginationMeta" class="mt-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
        <!-- Sort Controls -->
        <div class="flex items-center space-x-4">
          <label class="text-sm font-medium text-text-light dark:text-text-dark">Sort by:</label>
          <select
            v-model="currentFilters.sort_by"
            @change="handleSortChange(currentFilters.sort_by)"
            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-text-light dark:text-text-dark focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary focus:border-transparent"
          >
            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
              {{ option.label }}
              <span v-if="currentFilters.sort_by === option.value">
                ({{ currentFilters.sort_order === 'asc' ? '↑' : '↓' }})
              </span>
            </option>
          </select>
        </div>

        <!-- Pagination -->
        <div class="flex items-center space-x-2">
          <button
            v-for="page in paginationMeta.last_page"
            :key="page"
            @click="handlePageChange(page)"
            class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
            :class="[
              page === paginationMeta.current_page
                ? 'bg-primary dark:bg-dark-primary text-white'
                : 'bg-gray-100 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-200 dark:hover:bg-gray-600'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>

      <!-- Pagination Info -->
      <div v-if="paginationMeta" class="mt-4 text-sm text-gray-500 dark:text-gray-400 text-center">
        Showing {{ paginationMeta.from }} to {{ paginationMeta.to }} of {{ paginationMeta.total }} events
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, nextTick, computed, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CategoryPills from '@/components/events/CategoryPills.vue';
import EventViewToggle from '@/components/events/EventViewToggle.vue';
import AdvancedEventFilters from '@/components/events/AdvancedEventFilters.vue';
import EventService from '@/services/api/EventService';
import EventSearch from '@/components/events/EventSearch.vue';
import EventImage from '@/components/events/EventImage.vue';

interface Event {
  id: number;
  title: string;
  description: string;
  start_time: string;
  image_url?: string;
  category?: string;
  price?: number;
  location?: string;
}

interface Filters {
  dateRange: [Date, Date] | null;
  location: string;
  selectedCategories: string[];
  priceRange: [number, number];
  page: number;
  per_page: number;
  sort_by: string;
  sort_order: 'asc' | 'desc';
  search?: string;
}

interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

const route = useRoute();
const router = useRouter();
const events = ref<Event[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const resultsSection = ref<HTMLElement | null>(null);
const categories = ref<string[]>([]);
const selectedCategory = ref<string | null>(null);
const currentView = ref<'grid' | 'list'>('grid');
const paginationMeta = ref<PaginationMeta | null>(null);
const currentFilters = ref<Filters>({
  dateRange: null,
  location: '',
  selectedCategories: [],
  priceRange: [0, 1000],
  page: 1,
  per_page: 12,
  sort_by: 'start_time',
  sort_order: 'asc'
});
const searchQuery = ref('');

const sortOptions = [
  { value: 'start_time', label: 'Date' },
  { value: 'price', label: 'Price' },
  { value: 'title', label: 'Title' },
  { value: 'popularity', label: 'Popularity' }
];

// Get the EventService instance
const eventService = EventService.getInstance();

const handleViewChange = (view: 'grid' | 'list') => {
  currentView.value = view;
  // Update URL with the new view
  const query = { ...route.query };
  if (view === 'grid') {
    delete query.view;
  } else {
    query.view = view;
  }
  router.push({ query });
};

const handleAdvancedFilters = async (filters: Omit<Filters, 'page' | 'per_page' | 'sort_by' | 'sort_order'>) => {
  currentFilters.value = {
    ...currentFilters.value,
    ...filters,
    page: 1 // Reset to first page when filters change
  };
  loading.value = true;
  error.value = null;
  
  try {
    // Update URL with filter parameters
    const query = { ...route.query };
    if (filters.dateRange) {
      query.startDate = filters.dateRange[0].toISOString();
      query.endDate = filters.dateRange[1].toISOString();
    } else {
      delete query.startDate;
      delete query.endDate;
    }
    
    if (filters.location) {
      query.location = filters.location;
    } else {
      delete query.location;
    }
    
    if (filters.selectedCategories.length > 0) {
      query.categories = filters.selectedCategories.join(',');
    } else {
      delete query.categories;
    }
    
    if (filters.priceRange[0] !== 0 || filters.priceRange[1] !== 1000) {
      query.minPrice = filters.priceRange[0].toString();
      query.maxPrice = filters.priceRange[1].toString();
    } else {
      delete query.minPrice;
      delete query.maxPrice;
    }
    
    await router.push({ query });
    
    // Use EventService instance to fetch filtered events
    const response = await eventService.getEvents({
      category: selectedCategory.value,
      ...currentFilters.value,
    });
    events.value = response.data.data;
    paginationMeta.value = response.data.meta;
    
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
    // Use EventService instance to fetch filtered events
    const response = await eventService.getEvents({
      category,
      ...currentFilters.value,
    });
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
    // Use EventService instance to fetch all events with current filters
    const response = await eventService.getEvents(currentFilters.value);
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
    const response = await eventService.getCategories();
    categories.value = response.data.categories;
  } catch (err: unknown) {
    console.error('Failed to fetch categories:', err);
    error.value = 'Failed to load categories. Please try again later.';
  }
};

const handlePageChange = async (page: number) => {
  currentFilters.value.page = page;
  await fetchEvents();
};

const handleSortChange = async (sortBy: string) => {
  currentFilters.value.sort_by = sortBy;
  currentFilters.value.sort_order = currentFilters.value.sort_order === 'asc' ? 'desc' : 'asc';
  await fetchEvents();
};

const fetchEvents = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await eventService.getEvents({
      category: selectedCategory.value,
      ...currentFilters.value,
    });
    events.value = response.data.data;
    paginationMeta.value = response.data.meta;
  } catch (err: unknown) {
    console.error('Failed to fetch events:', err);
    error.value = 'Failed to load events. Please try again later.';
  } finally {
    loading.value = false;
  }
};

// Handle search query
const handleSearchQuery = async (query: string) => {
  loading.value = true;
  error.value = null;
  searchQuery.value = query;
  
  try {
    // Update URL with the search query
    const currentQuery = { ...route.query };
    if (query) {
      currentQuery.search = query;
    } else {
      delete currentQuery.search;
    }
    await router.push({ query: currentQuery });

    // Use EventService instance to fetch filtered events
    const response = await eventService.getEvents({
      ...currentFilters.value,
      search: query || undefined
    });
    
    events.value = response.data.data;
    paginationMeta.value = response.data.meta;

    // Scroll to results if we have a search query
    if (query) {
      await nextTick();
      scrollToResults();
    }
  } catch (err: unknown) {
    console.error('Failed to search events:', err);
    error.value = 'Failed to search events. Please try again later.';
  } finally {
    loading.value = false;
  }
};

// Mobile Filter States
const isMobile = ref(false);
const showMobileFilters = ref(false);

// Toggle mobile filters visibility
const toggleMobileFilters = () => {
  showMobileFilters.value = !showMobileFilters.value;
};

// Check window width on mount and resize
const checkIsMobile = () => {
  isMobile.value = window.innerWidth < 640; // sm breakpoint in Tailwind
};

// Computed properties for filters
const hasActiveFilters = computed(() => {
  return !!currentFilters.value.dateRange || 
    !!currentFilters.value.location || 
    currentFilters.value.selectedCategories.length > 0 || 
    currentFilters.value.priceRange[0] > 0 || 
    currentFilters.value.priceRange[1] < 1000;
});

const activeFiltersCount = computed(() => {
  let count = 0;
  if (currentFilters.value.dateRange) count++;
  if (currentFilters.value.location) count++;
  if (currentFilters.value.selectedCategories.length > 0) count++;
  if (currentFilters.value.priceRange[0] > 0 || currentFilters.value.priceRange[1] < 1000) count++;
  return count;
});

onMounted(() => {
  // Check if mobile on initial load
  checkIsMobile();

  // Set up event listener for window resize
  window.addEventListener('resize', checkIsMobile);

  // Get the category, view, and filters from URL query params if they exist
  const { category, view, startDate, endDate, location, categories, minPrice, maxPrice } = route.query;
  
  if (category && typeof category === 'string') {
    selectedCategory.value = category;
  }
  
  if (view && typeof view === 'string' && (view === 'grid' || view === 'list')) {
    currentView.value = view;
  }
  
  // Parse filters from URL
  if (startDate && endDate) {
    currentFilters.value.dateRange = [
      new Date(startDate as string),
      new Date(endDate as string)
    ];
  }
  
  if (location && typeof location === 'string') {
    currentFilters.value.location = location;
  }
  
  if (categories && typeof categories === 'string') {
    currentFilters.value.selectedCategories = categories.split(',');
  }
  
  if (minPrice && maxPrice) {
    currentFilters.value.priceRange = [
      parseInt(minPrice as string),
      parseInt(maxPrice as string)
    ];
  }
  
  Promise.all([
    fetchCategories(),
    fetchEvents()
  ]);
});

// Watch for route query changes to update selected category, view, and filters
watch(() => route.query, () => {
  const { category, view, startDate, endDate, location, categories, minPrice, maxPrice } = route.query;
  
  if (category && typeof category === 'string') {
    selectedCategory.value = category;
  } else {
    selectedCategory.value = null;
  }
  
  if (view && typeof view === 'string' && (view === 'grid' || view === 'list')) {
    currentView.value = view;
  }
  
  // Update filters from URL
  if (startDate && endDate) {
    currentFilters.value.dateRange = [
      new Date(startDate as string),
      new Date(endDate as string)
    ];
  } else {
    currentFilters.value.dateRange = null;
  }
  
  if (location && typeof location === 'string') {
    currentFilters.value.location = location;
  } else {
    currentFilters.value.location = '';
  }
  
  if (categories && typeof categories === 'string') {
    currentFilters.value.selectedCategories = categories.split(',');
  } else {
    currentFilters.value.selectedCategories = [];
  }
  
  if (minPrice && maxPrice) {
    currentFilters.value.priceRange = [
      parseInt(minPrice as string),
      parseInt(maxPrice as string)
    ];
  } else {
    currentFilters.value.priceRange = [0, 1000];
  }
}, { deep: true });

// Clean up event listeners on unmount
onUnmounted(() => {
  window.removeEventListener('resize', checkIsMobile);
});
</script>
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

    <!-- Search and category filters -->
    <div class="container mx-auto py-12">
      <EventSearch 
        :initial-query="searchQuery" 
        @search="handleSearch" 
      />
      
      <CategoryPills 
        :categories="categories" 
        :initial-category="selectedCategory" 
        @category-change="handleCategoryChange" 
      />
      
      <!-- Featured Events Section -->
      <div class="mt-16">
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
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 p-2 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-800 transition-colors z-10"
            aria-label="Previous slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary dark:text-dark-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button 
            @click="nextSlide" 
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 p-2 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-800 transition-colors z-10"
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
              class="w-2 h-2 rounded-full transition-colors duration-200"
              :class="[
                currentIndex === index 
                  ? 'bg-primary dark:bg-dark-primary' 
                  : 'bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500'
              ]"
              :aria-label="`Go to slide ${index + 1}`"
            ></button>
          </div>
        </div>
      </div>

      <!-- Upcoming/Search Results Events Section -->
      <div 
        class="mt-16 transition-all duration-300 rounded-2xl" 
        ref="searchResultsSection"
      >
        <div class="flex flex-col md:flex-row justify-between items-center mb-12">
          <h2 class="text-2xl font-bold text-text-light dark:text-text-dark mb-4 md:mb-0">
            <span class="border-b-4 border-primary dark:border-dark-primary pb-2">{{ searchQuery ? 'Search Results' : 'Upcoming Events' }}</span>
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
        
        <div v-else-if="events.length === 0" class="text-center py-12 bg-white dark:bg-background-dark/50 rounded-2xl shadow-md">
          <p class="text-text-light/70 dark:text-text-dark/70 mb-4">No events found</p>
          <button @click="resetFilters" class="text-primary dark:text-dark-primary hover:underline">Reset filters</button>
        </div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <EventCard 
            v-for="(event, index) in events" 
            :key="event.id" 
            :event="event" 
            :color-index="index" 
          />
        </div>
        
        <!-- Load More button -->
        <div v-if="events.length > 0 && hasMorePages" class="flex justify-center mt-12">
          <button 
            @click="loadMore" 
            class="bg-white dark:bg-background-dark/50 border border-gray-200 dark:border-gray-700 px-6 py-3 rounded-lg text-text-light/80 dark:text-text-dark/80 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            :disabled="loadingMore"
          >
            <span v-if="loadingMore">Loading...</span>
            <span v-else>Load More</span>
          </button>
        </div>
      </div>
    </div>
    
    <!-- How It Works Section -->
    <div class="bg-white dark:bg-background-dark/50 py-24">
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
    <div class="py-20 relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-accent-pink/20 dark:from-primary/10 dark:to-accent-pink/10"></div>
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
// Create placeholder/mock events for display when no real events exist yet
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import EventCard from '@/components/events/EventCard.vue';
import HorizontalEventScroller from '@/components/events/HorizontalEventScroller.vue';
import EventSearch from '@/components/events/EventSearch.vue';
import CategoryPills from '@/components/events/CategoryPills.vue';
import SkeletonLoader from '@/components/common/SkeletonLoader.vue';
import EventService, { type EventData } from '@/services/api/EventService';

// State for events
const events = ref<EventData[]>([]);
const allEvents = ref<EventData[]>([]); // Store all events for filtering
const featuredEvents = ref<EventData[]>([]);
const categorizedEvents = ref<{category: string, events: EventData[]}[]>([]);
const categories = ref<string[]>([]);

// Loading states
const isLoading = ref(true);
const loadingFeatured = ref(true);
const loadingCategorized = ref(true);
const loadingMore = ref(false);

// Pagination
const currentPage = ref(1);
const totalPages = ref(1);
const hasMorePages = computed(() => currentPage.value < totalPages.value);

// Filters
const searchQuery = ref('');
const selectedCategory = ref('');

// Popular categories to feature on the homepage
const popularCategories = ['Music', 'Conference', 'Workshop', 'Sports'];

// Placeholder event data - used when backend has no events yet
const placeholderEvents: EventData[] = [
  {
    id: 1,
    title: 'Summer Music Festival',
    description: 'Experience an unforgettable night of live music performances from top artists in a beautiful outdoor setting.',
    image_url: 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80',
    location: 'Central Park',
    category: 'Music',
    start_time: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(), // 1 week from now
    end_time: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000 + 5 * 60 * 60 * 1000).toISOString(),
    min_price: 25,
    max_price: 75,
    tickets_available: 500,
    organizer_id: 1,
    featured: true,
    is_published: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString()
  },
  {
    id: 2,
    title: 'Tech Innovation Summit',
    description: 'Join industry leaders and innovators to explore cutting-edge technologies and future trends.',
    image_url: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80',
    location: 'Tech Conference Center',
    category: 'Conference',
    start_time: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString(), // 2 weeks from now
    end_time: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000 + 8 * 60 * 60 * 1000).toISOString(),
    min_price: 50,
    max_price: 150,
    tickets_available: 300,
    organizer_id: 2,
    featured: true,
    is_published: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString()
  },
  {
    id: 3,
    title: 'Creative Writing Workshop',
    description: 'Enhance your storytelling skills with guidance from published authors in an interactive workshop.',
    image_url: 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80',
    location: 'City Library',
    category: 'Workshop',
    start_time: new Date(Date.now() + 5 * 24 * 60 * 60 * 1000).toISOString(), // 5 days from now
    end_time: new Date(Date.now() + 5 * 24 * 60 * 60 * 1000 + 3 * 60 * 60 * 1000).toISOString(),
    min_price: 15,
    max_price: 15,
    tickets_available: 50,
    organizer_id: 3,
    featured: true,
    is_published: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString()
  },
  {
    id: 4,
    title: 'Basketball Tournament',
    description: 'Watch local teams compete in an exciting basketball tournament with prizes and entertainment.',
    image_url: 'https://images.unsplash.com/photo-1546519638-68e109498ffc?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80',
    location: 'City Stadium',
    category: 'Sports',
    start_time: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString(), // 3 days from now
    end_time: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000 + 6 * 60 * 60 * 1000).toISOString(),
    min_price: 10,
    max_price: 30,
    tickets_available: 400,
    organizer_id: 4,
    featured: false,
    is_published: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString()
  },
  {
    id: 5,
    title: 'Art Exhibition: Modern Expressions',
    description: 'Explore contemporary artworks from emerging artists in this thought-provoking exhibition.',
    image_url: 'https://images.unsplash.com/photo-1531259683007-016a7b628fc3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80',
    location: 'Downtown Gallery',
    category: 'Exhibition',
    start_time: new Date(Date.now() + 10 * 24 * 60 * 60 * 1000).toISOString(), // 10 days from now
    end_time: new Date(Date.now() + 24 * 24 * 60 * 60 * 1000).toISOString(), // Runs for 2 weeks
    min_price: 0,
    max_price: 0,
    tickets_available: 1000,
    organizer_id: 5,
    featured: false,
    is_published: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString()
  },
  {
    id: 6,
    title: 'Food & Wine Festival',
    description: 'Savor delicious cuisine and fine wines from local restaurants and wineries at this gastronomic event.',
    image_url: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80',
    location: 'Riverside Park',
    category: 'Food & Drink',
    start_time: new Date(Date.now() + 21 * 24 * 60 * 60 * 1000).toISOString(), // 3 weeks from now
    end_time: new Date(Date.now() + 22 * 24 * 60 * 60 * 1000).toISOString(), // 2-day event
    min_price: 35,
    max_price: 85,
    tickets_available: 600,
    organizer_id: 6,
    featured: false,
    is_published: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString()
  },
  // Add three more events to have a total of 9 for a 3x3 grid
  {
    id: 7,
    title: 'Photography Workshop',
    description: 'Learn advanced photography techniques from professional photographers in this hands-on workshop.',
    image_url: 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80',
    location: 'Photography Studio',
    category: 'Workshop',
    start_time: new Date(Date.now() + 9 * 24 * 60 * 60 * 1000).toISOString(),
    end_time: new Date(Date.now() + 9 * 24 * 60 * 60 * 1000 + 6 * 60 * 60 * 1000).toISOString(),
    min_price: 45,
    max_price: 45,
    tickets_available: 30,
    organizer_id: 3,
    featured: false,
    is_published: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString()
  },
  {
    id: 8,
    title: 'Comedy Night',
    description: 'Enjoy a night of laughter with performances by top stand-up comedians.',
    image_url: 'https://images.unsplash.com/photo-1527224538127-2104bb71c51b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80',
    location: 'City Theater',
    category: 'Entertainment',
    start_time: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString(),
    end_time: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000 + 3 * 60 * 60 * 1000).toISOString(),
    min_price: 20,
    max_price: 40,
    tickets_available: 200,
    organizer_id: 5,
    featured: true,
    is_published: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString()
  },
  {
    id: 9,
    title: 'Yoga Retreat',
    description: 'Recharge your mind and body with this weekend yoga and meditation retreat.',
    image_url: 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80',
    location: 'Mountain Resort',
    category: 'Wellness',
    start_time: new Date(Date.now() + 15 * 24 * 60 * 60 * 1000).toISOString(),
    end_time: new Date(Date.now() + 17 * 24 * 60 * 60 * 1000).toISOString(),
    min_price: 120,
    max_price: 200,
    tickets_available: 50,
    organizer_id: 7,
    featured: false,
    is_published: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString()
  }
];

// Get the current date for "upcoming" filter
const today = new Date().toISOString();

// Create placeholder categorized events
const createPlaceholderCategorizedEvents = () => {
  return popularCategories.map(category => {
    const categoryEvents = placeholderEvents.filter(event => 
      event.category === category || 
      (category === 'Sports' && event.category === 'Sports') // Match exact or related categories
    );
    return {
      category,
      events: categoryEvents.length > 0 ? categoryEvents : [
        // Fallback placeholder if no events match this category
        {
          id: 100 + popularCategories.indexOf(category),
          title: `${category} Event Coming Soon`,
          description: `Stay tuned for upcoming ${category.toLowerCase()} events that will be available soon!`,
          image_url: '',
          location: 'To be announced',
          category: category,
          start_time: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
          end_time: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000 + 3 * 60 * 60 * 1000).toISOString(),
          min_price: 0,
          max_price: 0,
          tickets_available: 0,
          organizer_id: 1,
          featured: false,
          is_published: true,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        }
      ]
    };
  });
};

// Apply current filters to events
const applyFilters = () => {
  if (!allEvents.value.length) return;
  
  // If no search or category filter, show all events
  if (!searchQuery.value && !selectedCategory.value) {
    events.value = [...allEvents.value].slice(0, 9); // Initial 3x3 grid
    return;
  }
  
  let filteredEvents = [...allEvents.value];
  
  // Apply search filter if present
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filteredEvents = filteredEvents.filter(event => 
      event.title.toLowerCase().includes(query) || 
      event.description.toLowerCase().includes(query) ||
      event.location.toLowerCase().includes(query) ||
      event.category.toLowerCase().includes(query)
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
  } catch (error) {
    console.error('Error loading initial data:', error);
  }
});

onUnmounted(() => {
  stopAutoPlay();
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
    const response = await EventService.getCategories();
    // If no categories returned from backend, use placeholder categories
    if (!response.data.categories || response.data.categories.length === 0) {
      categories.value = popularCategories;
    } else {
      categories.value = response.data.categories || [];
    }
  } catch (error) {
    console.error('Error fetching categories:', error);
    // Use placeholder categories on error
    categories.value = popularCategories;
  }
};

// Fetch featured events
const fetchFeaturedEvents = async () => {
  loadingFeatured.value = true;
  try {
    const response = await EventService.getFeaturedEvents();
    // If no featured events returned, use placeholders
    if (!response.data.data || response.data.data.length === 0) {
      featuredEvents.value = placeholderEvents.filter(event => event.featured);
    } else {
      featuredEvents.value = response.data.data || [];
    }
  } catch (error) {
    console.error('Error fetching featured events:', error);
    // Use placeholder featured events on error
    featuredEvents.value = placeholderEvents.filter(event => event.featured);
  } finally {
    loadingFeatured.value = false;
  }
};

// Fetch all events for filtering
const fetchAllEvents = async () => {
  isLoading.value = true;
  try {
    // First try to get all events from the API with a larger limit
    const response = await EventService.getEvents({ per_page: 20 });
    
    if (!response.data.data || response.data.data.length === 0) {
      // If no events returned, use placeholders
      allEvents.value = placeholderEvents;
      events.value = placeholderEvents.slice(0, 9); // Show first 9 for 3x3 grid
    } else {
      // Use real events
      allEvents.value = response.data.data;
      events.value = response.data.data.slice(0, 9); // Show first 9 for 3x3 grid
    }
    totalPages.value = Math.ceil(allEvents.value.length / 9);
  } catch (error) {
    console.error('Error fetching all events:', error);
    // Use placeholder events on error
    allEvents.value = placeholderEvents;
    events.value = placeholderEvents.slice(0, 9);
    totalPages.value = Math.ceil(placeholderEvents.length / 9);
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
      // If no categories to fetch, use placeholder categorized events
      categorizedEvents.value = createPlaceholderCategorizedEvents();
    } else {
      const categoryPromises = categoriesToFetch.map(async (category) => {
        const response = await EventService.getEventsByCategory(category);
        
        // If no events in this category, use placeholders for this category
        const categoryEvents = (!response.data.data || response.data.data.length === 0) 
          ? placeholderEvents.filter(e => e.category === category)
          : response.data.data;
        
        return {
          category,
          events: categoryEvents.length > 0 ? categoryEvents : [
            // Fallback placeholder if no events match this category
            {
              id: 100 + popularCategories.indexOf(category),
              title: `${category} Event Coming Soon`,
              description: `Stay tuned for upcoming ${category.toLowerCase()} events that will be available soon!`,
              image_url: '',
              location: 'To be announced',
              category: category,
              start_time: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
              end_time: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000 + 3 * 60 * 60 * 1000).toISOString(),
              min_price: 0,
              max_price: 0,
              tickets_available: 0,
              organizer_id: 1,
              featured: false,
              is_published: true,
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString()
            }
          ]
        };
      });
      
      categorizedEvents.value = await Promise.all(categoryPromises);
    }
  } catch (error) {
    console.error('Error fetching categorized events:', error);
    // Use placeholder categorized events on error
    categorizedEvents.value = createPlaceholderCategorizedEvents();
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
    const startIndex = (currentPage.value - 1) * 9;
    let additionalEvents: EventData[] = [];
    
    if (searchQuery.value || selectedCategory.value) {
      // When filtering, we need to fetch more filtered events
      // This is client-side pagination of filtered results
      const filters = {
        page: currentPage.value,
        per_page: 9,
        search: searchQuery.value || undefined,
        category: selectedCategory.value === 'featured' || selectedCategory.value === 'upcoming' ? undefined : selectedCategory.value
      };
      
      try {
        let response;
        if (selectedCategory.value === 'featured') {
          response = await EventService.getFeaturedEvents(9);
          additionalEvents = response.data.data || [];
        } else if (selectedCategory.value === 'upcoming') {
          response = await EventService.getUpcomingEvents(9);
          additionalEvents = response.data.data || [];
        } else {
          response = await EventService.getEvents(filters);
          additionalEvents = response.data.data || [];
        }
      } catch (error) {
        console.error('Error fetching more filtered events:', error);
        additionalEvents = [];
      }
    } else {
      // When showing all events, just take the next page from allEvents
      additionalEvents = allEvents.value.slice(startIndex, startIndex + 9);
    }
    
    // If no more events returned from backend or local filtering, use more placeholders
    if (additionalEvents.length === 0 && currentPage.value === 2) {
      // Add more placeholder events with different IDs to simulate pagination
      additionalEvents = placeholderEvents.map(event => ({
        ...event,
        id: event.id + 100, // Add 100 to IDs to make them unique
        title: `${event.title} (Extended)`,
        start_time: new Date(new Date(event.start_time).getTime() + 30 * 24 * 60 * 60 * 1000).toISOString() // 30 days later
      }));
    }
    
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
  events.value = allEvents.value.slice(0, 9); // Reset to show first 9 events
};

// Carousel state
const currentIndex = ref(0);
const autoPlayInterval = ref<number | null>(null);

// Carousel methods
const nextSlide = () => {
  if (featuredEvents.value.length <= 1) return;
  currentIndex.value = (currentIndex.value + 1) % featuredEvents.value.length;
};

const prevSlide = () => {
  if (featuredEvents.value.length <= 1) return;
  currentIndex.value = currentIndex.value === 0 
    ? featuredEvents.value.length - 1 
    : currentIndex.value - 1;
};

const goToSlide = (index: number) => {
  currentIndex.value = index;
};

const startAutoPlay = () => {
  if (featuredEvents.value.length <= 1) return;
  stopAutoPlay(); // Clear any existing interval
  autoPlayInterval.value = window.setInterval(() => {
    nextSlide();
  }, 5000); // Change slide every 5 seconds
};

const stopAutoPlay = () => {
  if (autoPlayInterval.value) {
    clearInterval(autoPlayInterval.value);
    autoPlayInterval.value = null;
  }
};

const pauseAutoPlay = () => {
  stopAutoPlay();
};

const resumeAutoPlay = () => {
  startAutoPlay();
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
  } catch (error) {
    console.error('Error loading initial data:', error);
  }
});

onUnmounted(() => {
  stopAutoPlay();
  document.removeEventListener('visibilitychange', () => {});
});

// Watch for filter changes and featured events
watch([searchQuery, selectedCategory], () => {
  currentPage.value = 1;
  applyFilters();
});

watch(featuredEvents, (newEvents) => {
  if (newEvents.length > 0) {
    currentIndex.value = 0; // Reset to first slide when events change
    startAutoPlay(); // Restart autoplay
  } else {
    stopAutoPlay(); // Stop autoplay if no events
  }
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
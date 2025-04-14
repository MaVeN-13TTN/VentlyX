<template>
  <div class="bg-background-light dark:bg-background-dark min-h-screen pb-12">
    <div v-if="loading" class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="animate-pulse space-y-8">
        <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
        <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="space-y-4">
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-4/6"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/6"></div>
        </div>
      </div>
    </div>

    <div v-else-if="error" class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
      <div class="bg-red-50 dark:bg-red-900/10 p-4 rounded-lg">
        <p class="text-red-600 dark:text-red-400">{{ error }}</p>
        <button 
          @click="fetchEvent" 
          class="mt-4 px-4 py-2 bg-primary dark:bg-dark-primary text-white rounded-lg hover:bg-primary-dark dark:hover:bg-dark-primary-dark transition-colors"
        >
          Try Again
        </button>
      </div>
    </div>

    <div v-else class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- SEO Structured Data -->
      <script type="application/ld+json">
        {{
          JSON.stringify({
            '@context': 'https://schema.org',
            '@type': 'Event',
            'name': event.title,
            'startDate': event.start_time,
            'endDate': event.end_time,
            'location': {
              '@type': 'Place',
              'name': event.location,
              'address': {
                '@type': 'PostalAddress',
                'addressLocality': event.location
              }
            },
            'image': event.image_url,
            'description': event.description,
            'offers': {
              '@type': 'Offer',
              'price': event.price,
              'priceCurrency': 'USD',
              'availability': 'https://schema.org/InStock',
              'url': window.location.href
            },
            'organizer': {
              '@type': 'Organization',
              'name': event.organizer?.name || 'VentlyX Event'
            }
          })
        }}
      </script>

      <!-- Event Header -->
      <div class="mb-8">
        <div class="flex items-center mb-2">
          <span class="text-xs px-2 py-1 bg-primary/10 dark:bg-dark-primary/10 text-primary dark:text-dark-primary rounded-full font-medium">
            {{ event.category }}
          </span>
          <span v-if="event.featuredEvent" class="ml-2 text-xs px-2 py-1 bg-amber-100 dark:bg-amber-900/20 text-amber-800 dark:text-amber-400 rounded-full font-medium">
            Featured
          </span>
          <div class="ml-auto flex space-x-2">
            <div class="inline-flex rounded-md" role="group">
              <ShareEvent 
                :title="event.title" 
                :url="shareUrl" 
                class="rounded-l-md"
              />
              <CalendarOptions
                :title="event.title"
                :description="event.description"
                :location="event.location"
                :start-time="event.start_time"
                :end-time="event.end_time"
                class="rounded-r-md border-l-0"
              />
            </div>
          </div>
        </div>
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-text-light dark:text-text-dark">{{ event.title }}</h1>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <!-- Image Gallery with Lightbox -->
          <EventGallery 
            :images="event.images"
            :main-image="event.image_url"
            :alt-text="event.title"
          />

          <!-- Event Description -->
          <div class="mb-8">
            <h2 class="text-xl md:text-2xl font-semibold text-text-light dark:text-text-dark mb-4">About This Event</h2>
            <div class="prose prose-sm sm:prose lg:prose-lg dark:prose-invert max-w-none">
              {{ event.description }}
            </div>
          </div>

          <!-- Location with Map -->
          <div class="mb-8">
            <h2 class="text-xl md:text-2xl font-semibold text-text-light dark:text-text-dark mb-4">Location</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
              <p class="mb-2 text-text-light dark:text-text-dark">{{ event.location }}</p>
              
              <!-- Map Container -->
              <div ref="mapContainer" class="h-64 rounded-lg overflow-hidden"></div>
            </div>
          </div>

          <!-- Organizer Details -->
          <div class="mb-8">
            <h2 class="text-xl md:text-2xl font-semibold text-text-light dark:text-text-dark mb-4">Organizer</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden mr-4">
                  <img v-if="event.organizer?.avatar" :src="event.organizer.avatar" alt="Organizer avatar" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-gray-500 dark:text-gray-400 text-xl font-medium">
                    {{ event.organizer?.name?.charAt(0) || 'O' }}
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-text-light dark:text-text-dark">{{ event.organizer?.name || 'Event Organizer' }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.organizer?.events_count || '0' }} Events</p>
                </div>
                <router-link 
                  v-if="event.organizer?.id" 
                  :to="{ name: 'organizer-profile', params: { id: event.organizer.id }}" 
                  class="ml-auto text-primary dark:text-dark-primary hover:underline text-sm font-medium"
                >
                  View Profile
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-1">
          <!-- Event Details Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 sticky top-8">
            <!-- Date and Time -->
            <div class="mb-6">
              <div class="flex items-start">
                <div class="p-2 bg-primary/10 dark:bg-dark-primary/10 rounded-lg mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary dark:text-dark-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-text-light dark:text-text-dark">Date & Time</h3>
                  <p class="text-gray-600 dark:text-gray-300">{{ formatDateTime(event.start_time, 'full') }}</p>
                  <p v-if="event.end_time" class="text-gray-500 dark:text-gray-400 text-sm">
                    to {{ formatDateTime(event.end_time, 'time') }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Location Summary -->
            <div class="mb-6">
              <div class="flex items-start">
                <div class="p-2 bg-primary/10 dark:bg-dark-primary/10 rounded-lg mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary dark:text-dark-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-text-light dark:text-text-dark">Location</h3>
                  <p class="text-gray-600 dark:text-gray-300">{{ event.location }}</p>
                </div>
              </div>
            </div>

            <!-- Ticket Types -->
            <div class="mb-6">
              <h3 class="font-semibold text-text-light dark:text-text-dark mb-3">Tickets</h3>
              <div v-if="event.ticket_types && event.ticket_types.length > 0">
                <div 
                  v-for="ticket in event.ticket_types" 
                  :key="ticket.id" 
                  class="mb-2 p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
                >
                  <div class="flex justify-between">
                    <div>
                      <h4 class="font-medium text-text-light dark:text-text-dark">{{ ticket.name }}</h4>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ ticket.description }}</p>
                    </div>
                    <div class="text-right">
                      <p class="font-semibold text-text-light dark:text-text-dark">${{ ticket.price.toFixed(2) }}</p>
                      <span 
                        :class="[
                          'text-xs px-2 py-0.5 rounded-full',
                          ticket.available_quantity > 10 
                            ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400'
                            : ticket.available_quantity > 0 
                              ? 'bg-amber-100 dark:bg-amber-900/20 text-amber-800 dark:text-amber-400'
                              : 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-400'
                        ]"
                      >
                        {{ 
                          ticket.available_quantity > 0 
                            ? ticket.available_quantity > 10 
                              ? 'Available'
                              : `Only ${ticket.available_quantity} left` 
                            : 'Sold out' 
                        }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-3 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                <p class="text-gray-500 dark:text-gray-400">No ticket information available</p>
              </div>
            </div>

            <!-- CTA Button -->
            <button 
              @click="buyTickets" 
              class="w-full py-3 px-6 bg-primary dark:bg-dark-primary text-white rounded-lg hover:bg-primary-dark dark:hover:bg-dark-primary-dark transition-colors flex items-center justify-center space-x-2"
              :disabled="!hasAvailableTickets"
              :class="{ 'opacity-50 cursor-not-allowed': !hasAvailableTickets }"
            >
              <span>{{ hasAvailableTickets ? 'Buy Tickets' : 'Sold Out' }}</span>
              <svg v-if="hasAvailableTickets" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Related Events -->
      <div class="mt-12">
        <h2 class="text-2xl font-semibold text-text-light dark:text-text-dark mb-6">You May Also Like</h2>
        <div v-if="relatedEvents.length > 0" class="relative">
          <!-- Navigation Buttons -->
          <button 
            @click="scrollCarousel('left')" 
            class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-gray-800 p-2 rounded-full shadow-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          
          <div ref="carouselContainer" class="overflow-x-scroll scrollbar-hide scroll-smooth">
            <div class="inline-flex space-x-6 py-4 px-1">
              <div 
                v-for="relatedEvent in relatedEvents" 
                :key="relatedEvent.id"
                class="w-72 flex-shrink-0"
              >
                <router-link :to="{ name: 'event-details', params: { id: relatedEvent.id }}">
                  <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
                    <div class="h-40 relative overflow-hidden">
                      <img 
                        v-if="relatedEvent.image_url" 
                        :src="relatedEvent.image_url" 
                        :alt="relatedEvent.title" 
                        class="w-full h-full object-cover"
                      />
                      <div v-else class="w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                        <span class="text-gray-400 dark:text-gray-500">No image</span>
                      </div>
                    </div>
                    <div class="p-4">
                      <p class="text-xs text-primary dark:text-dark-primary mb-1">{{ relatedEvent.category }}</p>
                      <h3 class="font-medium text-text-light dark:text-text-dark line-clamp-2">{{ relatedEvent.title }}</h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ formatDateTime(relatedEvent.start_time, 'short') }}
                      </p>
                      <div class="flex justify-between items-center mt-3">
                        <span class="text-sm text-text-light dark:text-text-dark">{{ relatedEvent.location }}</span>
                        <span class="font-medium text-text-light dark:text-text-dark">${{ relatedEvent.price?.toFixed(2) || 'Free' }}</span>
                      </div>
                    </div>
                  </div>
                </router-link>
              </div>
            </div>
          </div>
          
          <button 
            @click="scrollCarousel('right')" 
            class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-gray-800 p-2 rounded-full shadow-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
        <div v-else class="text-center py-8">
          <p class="text-gray-500 dark:text-gray-400">No related events found</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import EventService from '@/services/api/EventService';
import { useNotificationStore } from '@/stores/notification';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import EventGallery from '@/components/events/EventGallery.vue';
import ShareEvent from '@/components/events/ShareEvent.vue';
import CalendarOptions from '@/components/events/CalendarOptions.vue';

// Initialize services and stores
const eventService = EventService.getInstance();
const notificationStore = useNotificationStore();
const route = useRoute();
const router = useRouter();

// Setup state variables
const event = ref<any>({});
const loading = ref(true);
const error = ref('');
const relatedEvents = ref<any[]>([]);
const shareUrl = ref('');
const mapContainer = ref<HTMLElement | null>(null);
const carouselContainer = ref<HTMLElement | null>(null);
let map: any = null;

// Computed properties
const hasAvailableTickets = computed(() => {
  if (!event.value.ticket_types || event.value.ticket_types.length === 0) return false;
  return event.value.ticket_types.some((ticket: any) => ticket.available_quantity > 0);
});

// Fetch the event data
const fetchEvent = async () => {
  const eventId = Number(route.params.id);
  if (!eventId) {
    error.value = 'Invalid event ID';
    loading.value = false;
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const response = await eventService.getEvent(eventId);
    event.value = response.data;
    shareUrl.value = window.location.href;
    
    // Fetch related events
    fetchRelatedEvents();
    
    // Initialize map after we have the location data
    nextTick(() => {
      initMap();
    });
  } catch (err: any) {
    error.value = err.message || 'Failed to load event details';
    console.error('Error fetching event details:', err);
  } finally {
    loading.value = false;
  }
};

// Fetch related events based on category or tags
const fetchRelatedEvents = async () => {
  try {
    const filter: any = {};
    if (event.value.category) {
      filter.category = event.value.category;
    }
    
    // Exclude current event
    const response = await eventService.getEvents(filter);
    relatedEvents.value = response.data.data
      .filter((e: any) => e.id !== event.value.id)
      .slice(0, 8); // Limit to 8 related events
  } catch (err) {
    console.error('Error fetching related events:', err);
  }
};

// Format date and time
const formatDateTime = (dateString: string, format: 'full' | 'short' | 'time') => {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  
  if (format === 'full') {
    return new Intl.DateTimeFormat('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric'
    }).format(date);
  } else if (format === 'short') {
    return new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric'
    }).format(date);
  } else {
    return new Intl.DateTimeFormat('en-US', {
      hour: 'numeric',
      minute: 'numeric'
    }).format(date);
  }
};

// Initialize the map with Leaflet
const initMap = async () => {
  if (!mapContainer.value || !event.value.location) return;
  
  // Check if map is already initialized
  if (map) {
    map.remove();
  }
  
  // Convert the location to coordinates using a geocoding service
  try {
    // This is a simple implementation - a real app might use a service like Mapbox or Google
    const locationQuery = encodeURIComponent(event.value.location);
    const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${locationQuery}&format=json&limit=1`);
    const data = await response.json();
    
    if (data && data.length > 0) {
      const { lat, lon } = data[0];
      
      // Create the map
      map = L.map(mapContainer.value).setView([lat, lon], 13);
      
      // Add the tile layer (OpenStreetMap)
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);
      
      // Add a marker for the event location
      L.marker([lat, lon])
        .addTo(map)
        .bindPopup(event.value.location)
        .openPopup();
    }
  } catch (err) {
    console.error('Error initializing map:', err);
  }
};

// Related events carousel
const scrollCarousel = (direction: 'left' | 'right') => {
  if (!carouselContainer.value) return;
  
  const scrollAmount = 300; // pixels to scroll
  
  if (direction === 'left') {
    carouselContainer.value.scrollLeft -= scrollAmount;
  } else {
    carouselContainer.value.scrollLeft += scrollAmount;
  }
};

// Buy tickets function
const buyTickets = () => {
  router.push({ name: 'event-booking', params: { id: event.value.id } });
};

// Lifecycle hooks
onMounted(() => {
  fetchEvent();
});

onUnmounted(() => {
  if (map) {
    map.remove();
    map = null;
  }
});

// Watch for route changes to reload data
watch(() => route.params.id, () => {
  fetchEvent();
});
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}

/* Override Leaflet default style */
:global(.leaflet-control-attribution) {
  font-size: 8px !important;
}
</style>
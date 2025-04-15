<template>
  <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-md overflow-hidden transform hover:-translate-y-2 transition-all duration-300 border border-gray-100 dark:border-gray-700">
    <div class="relative h-64 overflow-hidden rounded-t-2xl">
      <div class="absolute inset-0" :class="gradientClass"></div>
      <img v-if="event.image_url" :src="event.image_url" :alt="event.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
      <div v-else class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-gray-700">
        <span class="text-gray-400 dark:text-gray-500">No Image</span>
      </div>
      <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/40">
        <span class="bg-white/10 backdrop-blur-sm text-white px-4 py-2 rounded-lg">View Details</span>
      </div>

      <!-- Availability Badge -->
      <div v-if="event.ticket_types && event.ticket_types.length > 0" class="absolute bottom-3 right-3">
        <span
          class="px-2 py-1 text-xs font-medium rounded-full"
          :class="availabilityClass"
        >
          {{ availabilityText }}
        </span>
      </div>
    </div>
    <div class="p-6">
      <div class="flex items-center space-x-2 mb-3">
        <span class="text-xs" :class="categoryClass">{{ event.category }}</span>
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(event.start_time) }}</span>
      </div>
      <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white line-clamp-2">{{ event.title }}</h3>
      <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-2 text-sm">{{ event.description }}</p>
      <div class="flex justify-between items-center">
        <span class="text-primary-500 dark:text-primary-400 font-medium">{{ formatPrice(event.min_price) }}</span>
        <router-link
          :to="`/events/${event.id}`"
          class="bg-primary-500 dark:bg-primary-400 text-white px-4 py-2 rounded-full
         hover:shadow-md hover:scale-105 hover:bg-primary-600
         transition-all duration-200"
        >
          Book Now
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { EventData } from '@/services/api/EventService';

const props = defineProps<{
  event: EventData;
  colorIndex?: number;
}>();

// Generate different gradient colors based on colorIndex or category
const gradientClass = computed(() => {
  const gradients = [
    'bg-gradient-to-br from-pink-500/30 to-blue-500/30',
    'bg-gradient-to-br from-yellow-500/30 to-blue-500/30',
    'bg-gradient-to-br from-pink-500/30 to-yellow-500/30',
    'bg-gradient-to-br from-primary-500/30 to-pink-500/30',
    'bg-gradient-to-br from-primary-500/30 to-yellow-500/30'
  ];
  return gradients[(props.colorIndex || 0) % gradients.length];
});

// Generate different category badge styling based on category
const categoryClass = computed(() => {
  const categories: Record<string, string> = {
    'Music': 'bg-primary-500/10 text-primary-500 dark:bg-primary-400/10 dark:text-primary-400 px-2 py-1 rounded-full',
    'Conference': 'bg-yellow-500/10 text-yellow-500 dark:bg-yellow-400/10 dark:text-yellow-400 px-2 py-1 rounded-full',
    'Workshop': 'bg-pink-500/10 text-pink-500 dark:bg-pink-400/10 dark:text-pink-400 px-2 py-1 rounded-full',
    'Sports': 'bg-blue-500/10 text-blue-500 dark:bg-blue-400/10 dark:text-blue-400 px-2 py-1 rounded-full',
    'Exhibition': 'bg-green-500/10 text-green-500 dark:bg-green-400/10 dark:text-green-400 px-2 py-1 rounded-full'
  };

  return categories[props.event.category] || 'bg-gray-500/10 text-gray-500 dark:bg-gray-400/10 dark:text-gray-400 px-2 py-1 rounded-full';
});

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// Compute availability class and text based on ticket types
const availabilityClass = computed(() => {
  if (!props.event.ticket_types || props.event.ticket_types.length === 0) {
    return 'bg-gray-500/80 text-white';
  }

  const totalAvailable = props.event.ticket_types.reduce((sum, ticket) => sum + (ticket.available_quantity || 0), 0);

  if (totalAvailable === 0) {
    return 'bg-red-500/80 text-white';
  } else if (totalAvailable < 10) {
    return 'bg-amber-500/80 text-white';
  } else {
    return 'bg-green-500/80 text-white';
  }
});

const availabilityText = computed(() => {
  if (!props.event.ticket_types || props.event.ticket_types.length === 0) {
    return 'Unavailable';
  }

  const totalAvailable = props.event.ticket_types.reduce((sum, ticket) => sum + (ticket.available_quantity || 0), 0);

  if (totalAvailable === 0) {
    return 'Sold Out';
  } else if (totalAvailable < 10) {
    return `${totalAvailable} left`;
  } else {
    return 'Available';
  }
});

const formatPrice = (price: number) => {
  return price ? `$${price.toFixed(2)}` : 'Free';
};
</script>
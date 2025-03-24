<template>
  <div class="relative">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">
      <span class="border-b-4 border-primary-500 dark:border-primary-400 pb-2">{{ title }}</span>
    </h2>
    
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <SkeletonLoader v-for="i in 3" :key="i" type="card" :animated="true" />
    </div>
    
    <div v-else-if="events.length === 0" class="text-center py-12">
      <p class="text-gray-600 dark:text-gray-300">No events found</p>
    </div>
    
    <div v-else class="relative group">
      <!-- Navigation arrows -->
      <button 
        v-if="events.length > 3"
        class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-6 bg-white dark:bg-gray-800 rounded-full p-2 shadow-md z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 hover:bg-gray-100 dark:hover:bg-gray-700"
        @click="scroll(-1)"
        aria-label="Scroll left"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      
      <div ref="scrollContainer" class="overflow-x-auto no-scrollbar scroll-smooth">
        <div class="grid grid-flow-col auto-cols-max gap-8">
          <EventCard 
            v-for="(event, index) in events" 
            :key="event.id" 
            :event="event" 
            :color-index="index"
            class="w-80"
          />
        </div>
      </div>
      
      <button 
        v-if="events.length > 3"
        class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-6 bg-white dark:bg-gray-800 rounded-full p-2 shadow-md z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 hover:bg-gray-100 dark:hover:bg-gray-700"
        @click="scroll(1)"
        aria-label="Scroll right"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import EventCard from './EventCard.vue';
import SkeletonLoader from '../common/SkeletonLoader.vue';
import type { EventData } from '@/services/api/EventService';

const props = defineProps<{
  title: string;
  events: EventData[];
  loading: boolean;
}>();

const scrollContainer = ref<HTMLElement | null>(null);

const scroll = (direction: number) => {
  if (!scrollContainer.value) return;
  const scrollAmount = direction * 340; // Approximate width of a card + gap
  scrollContainer.value.scrollBy({
    left: scrollAmount,
    behavior: 'smooth'
  });
};
</script>

<style scoped>
.no-scrollbar {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}
.no-scrollbar::-webkit-scrollbar {
  display: none;  /* Chrome, Safari, Opera */
}
</style>
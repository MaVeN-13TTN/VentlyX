<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-medium text-text-light dark:text-text-dark mb-3">Event Begins In:</h3>
    
    <div v-if="eventHasStarted" class="text-center py-2">
      <div class="text-primary dark:text-dark-primary font-bold text-xl">Event has started!</div>
      <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">{{ eventStartedTimeAgo }}</p>
    </div>
    
    <div v-else class="grid grid-cols-4 gap-2">
      <div class="flex flex-col items-center">
        <div class="text-2xl md:text-3xl font-bold text-primary dark:text-dark-primary">{{ timeLeft.days }}</div>
        <div class="text-xs md:text-sm text-gray-600 dark:text-gray-400 uppercase">Days</div>
      </div>
      <div class="flex flex-col items-center">
        <div class="text-2xl md:text-3xl font-bold text-primary dark:text-dark-primary">{{ timeLeft.hours }}</div>
        <div class="text-xs md:text-sm text-gray-600 dark:text-gray-400 uppercase">Hours</div>
      </div>
      <div class="flex flex-col items-center">
        <div class="text-2xl md:text-3xl font-bold text-primary dark:text-dark-primary">{{ timeLeft.minutes }}</div>
        <div class="text-xs md:text-sm text-gray-600 dark:text-gray-400 uppercase">Mins</div>
      </div>
      <div class="flex flex-col items-center">
        <div class="text-2xl md:text-3xl font-bold text-primary dark:text-dark-primary">{{ timeLeft.seconds }}</div>
        <div class="text-xs md:text-sm text-gray-600 dark:text-gray-400 uppercase">Secs</div>
      </div>
    </div>
    
    <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
      <div class="flex items-center text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 dark:text-gray-400 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <div class="text-gray-600 dark:text-gray-400">
          <span>{{ formattedStartDate }}</span>
          <span class="mx-2">•</span>
          <span>{{ formattedStartTime }}</span>
        </div>
      </div>
      
      <div v-if="eventLocation" class="flex items-center text-sm mt-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 dark:text-gray-400 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <div class="text-gray-600 dark:text-gray-400">{{ eventLocation }}</div>
      </div>
    </div>
    
    <div v-if="showAddToCalendar" class="mt-4">
      <button 
        @click="addToCalendar"
        class="w-full py-2 px-4 bg-primary/10 dark:bg-dark-primary/20 text-primary dark:text-dark-primary text-sm rounded-md hover:bg-primary/20 dark:hover:bg-dark-primary/30 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-dark-primary flex items-center justify-center"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        Add to Calendar
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, PropType } from 'vue';
import { useNotificationStore } from '@/stores/notification';

// Props
const props = defineProps({
  startDateTime: {
    type: String,
    required: true
  },
  eventName: {
    type: String,
    required: true
  },
  eventLocation: {
    type: String,
    default: ''
  },
  eventEndDateTime: {
    type: String,
    default: ''
  },
  showAddToCalendar: {
    type: Boolean,
    default: true
  }
});

// Store
const notificationStore = useNotificationStore();

// Reactive state
const now = ref(new Date());
const timerInterval = ref<number | null>(null);

// Computed properties
const startDate = computed(() => new Date(props.startDateTime));

const eventHasStarted = computed(() => {
  return now.value > startDate.value;
});

const eventStartedTimeAgo = computed(() => {
  if (!eventHasStarted.value) return '';
  
  const diffInSeconds = Math.floor((now.value.getTime() - startDate.value.getTime()) / 1000);
  
  if (diffInSeconds < 60) {
    return 'Started just now';
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60);
    return `Started ${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`;
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600);
    return `Started ${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
  } else {
    const days = Math.floor(diffInSeconds / 86400);
    return `Started ${days} ${days === 1 ? 'day' : 'days'} ago`;
  }
});

const timeLeft = computed(() => {
  const diff = Math.max(0, startDate.value.getTime() - now.value.getTime());
  
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);
  
  return {
    days: days.toString().padStart(2, '0'),
    hours: hours.toString().padStart(2, '0'),
    minutes: minutes.toString().padStart(2, '0'),
    seconds: seconds.toString().padStart(2, '0')
  };
});

const formattedStartDate = computed(() => {
  return new Intl.DateTimeFormat('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).format(startDate.value);
});

const formattedStartTime = computed(() => {
  return new Intl.DateTimeFormat('en-US', {
    hour: 'numeric',
    minute: 'numeric',
    hour12: true
  }).format(startDate.value);
});

// Methods
const updateTimer = () => {
  now.value = new Date();
};

const addToCalendar = () => {
  try {
    // Create calendar object with event details
    const calendarData = {
      title: props.eventName,
      start: startDate.value.toISOString(),
      end: props.eventEndDateTime 
        ? new Date(props.eventEndDateTime).toISOString() 
        : new Date(startDate.value.getTime() + 2 * 60 * 60 * 1000).toISOString(), // Default 2 hours duration
      location: props.eventLocation
    };
    
    // In a real app, you'd generate a calendar file or link to a calendar service
    // For now, we'll just show a notification
    console.log('Calendar data:', calendarData);
    notificationStore.showNotification('Added to your calendar', 'success');
    
  } catch (error) {
    console.error('Error adding to calendar:', error);
    notificationStore.showNotification('Failed to add to calendar', 'error');
  }
};

// Lifecycle hooks
onMounted(() => {
  updateTimer();
  timerInterval.value = window.setInterval(updateTimer, 1000);
});

onBeforeUnmount(() => {
  if (timerInterval.value !== null) {
    clearInterval(timerInterval.value);
  }
});
</script>
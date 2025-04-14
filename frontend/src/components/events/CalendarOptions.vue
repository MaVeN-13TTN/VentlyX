<template>
  <div class="relative">
    <button 
      @click="showOptions = !showOptions" 
      class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      Calendar
    </button>

    <!-- Calendar Options Dropdown -->
    <div 
      v-if="showOptions" 
      class="absolute right-0 z-10 mt-2 top-full w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
    >
      <div class="py-1">
        <a 
          href="#" 
          @click.prevent="addToCalendar('google')" 
          class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          Google Calendar
        </a>
        <a 
          href="#" 
          @click.prevent="addToCalendar('apple')" 
          class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          Apple Calendar
        </a>
        <a 
          href="#" 
          @click.prevent="addToCalendar('outlook')" 
          class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          Outlook
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useNotificationStore } from '@/stores/notification';

interface Props {
  title: string;
  location: string;
  description: string;
  startTime: string;
  endTime?: string;
}

const props = defineProps<Props>();
const notificationStore = useNotificationStore();

const showOptions = ref(false);

// Add to calendar function
const addToCalendar = (calendarType: string) => {
  const title = encodeURIComponent(props.title);
  const location = encodeURIComponent(props.location || '');
  const description = encodeURIComponent(props.description || '');
  let calendarUrl = '';

  const startDate = new Date(props.startTime);
  const endDate = props.endTime 
    ? new Date(props.endTime) 
    : new Date(startDate.getTime() + 60 * 60 * 1000); // Default to 1 hour if no end time

  // Format for Google Calendar
  const startIso = startDate.toISOString().replace(/-|:|\.\d+/g, '');
  const endIso = endDate.toISOString().replace(/-|:|\.\d+/g, '');

  switch (calendarType) {
    case 'google':
      calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${startIso}/${endIso}&details=${description}&location=${location}`;
      break;
    case 'apple':
      // For Apple Calendar, we create an .ics file
      const icsFile = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'BEGIN:VEVENT',
        `SUMMARY:${props.title}`,
        `DTSTART:${startIso}`,
        `DTEND:${endIso}`,
        `LOCATION:${props.location || ''}`,
        `DESCRIPTION:${props.description || ''}`,
        'END:VEVENT',
        'END:VCALENDAR'
      ].join('\n');

      const blob = new Blob([icsFile], { type: 'text/calendar;charset=utf-8' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `${props.title.replace(/\s+/g, '-')}.ics`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      notificationStore.success('Calendar file downloaded. Open it with your calendar app.');
      break;
    case 'outlook':
      // Similar to Google Calendar
      calendarUrl = `https://outlook.live.com/calendar/0/deeplink/compose?subject=${title}&startdt=${startDate.toISOString()}&enddt=${endDate.toISOString()}&body=${description}&location=${location}`;
      break;
  }

  if (calendarUrl) {
    window.open(calendarUrl, '_blank');
  }

  showOptions.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (e: MouseEvent) => {
  if (showOptions.value) {
    showOptions.value = false;
  }
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
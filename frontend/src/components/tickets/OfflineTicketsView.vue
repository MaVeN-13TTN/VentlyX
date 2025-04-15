<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-text-light dark:text-text-dark">
        Offline Tickets
      </h3>
      <div class="text-sm text-gray-500 dark:text-gray-400">
        <span v-if="lastUpdated">Last updated: {{ formatLastUpdated(lastUpdated) }}</span>
      </div>
    </div>
    
    <div v-if="!hasTickets" class="text-center py-8">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-gray-600 dark:text-gray-400 mb-2">No offline tickets available</p>
      <p class="text-sm text-gray-500 dark:text-gray-500">
        Your tickets will be saved for offline access when you view them online
      </p>
    </div>
    
    <div v-else>
      <div v-if="isOnline" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
        <p class="text-sm text-green-700 dark:text-green-400">
          <span class="font-medium">You're back online!</span> Your offline tickets are still available below.
        </p>
      </div>
      
      <div v-else class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-4">
        <p class="text-sm text-amber-700 dark:text-amber-400">
          <span class="font-medium">You're offline.</span> Your saved tickets are still available below.
        </p>
      </div>
      
      <div class="space-y-4">
        <div 
          v-for="ticket in tickets" 
          :key="ticket.booking_id"
          class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors cursor-pointer"
          @click="viewTicket(ticket)"
        >
          <div class="flex items-start">
            <div class="flex-1">
              <h4 class="font-medium text-text-light dark:text-text-dark mb-1">
                {{ ticket.booking?.event?.title || 'Event Ticket' }}
              </h4>
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                {{ formatDateTime(ticket.booking?.event?.start_time) }}
              </p>
              <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                <span class="mr-2">Ref: {{ ticket.ticket_number }}</span>
                <span>Qty: {{ ticket.booking?.quantity || 1 }}</span>
              </div>
            </div>
            
            <div class="ml-4">
              <div class="bg-white p-2 rounded border border-gray-200 dark:border-gray-700">
                <img 
                  v-if="ticket.qr_code_url" 
                  :src="ticket.qr_code_url" 
                  alt="Ticket QR Code" 
                  class="w-16 h-16"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import TicketService from '@/services/api/TicketService';
import type { Ticket } from '@/services/api/TicketService';

// Services
const router = useRouter();
const ticketService = TicketService.getInstance();

// State
const tickets = ref<Ticket[]>([]);
const lastUpdated = ref<Date | null>(null);
const isOnline = ref(navigator.onLine);

// Computed
const hasTickets = computed(() => tickets.value.length > 0);

// Methods
const loadOfflineTickets = () => {
  tickets.value = ticketService.getOfflineTickets();
  lastUpdated.value = ticketService.getLastTicketUpdateTime();
};

const formatLastUpdated = (date: Date) => {
  // If less than 24 hours ago, show relative time
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffHours = diffMs / (1000 * 60 * 60);
  
  if (diffHours < 24) {
    if (diffHours < 1) {
      const diffMinutes = Math.floor(diffMs / (1000 * 60));
      return `${diffMinutes} minute${diffMinutes !== 1 ? 's' : ''} ago`;
    }
    const hours = Math.floor(diffHours);
    return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
  }
  
  // Otherwise show date
  return date.toLocaleDateString();
};

const formatDateTime = (dateString?: string) => {
  if (!dateString) return 'Date not specified';
  
  try {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      weekday: 'short',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  } catch (error) {
    return 'Invalid date';
  }
};

const viewTicket = (ticket: Ticket) => {
  if (ticket.booking_id) {
    router.push({ name: 'ticket-details', params: { id: ticket.booking_id.toString() } });
  }
};

const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine;
};

// Lifecycle hooks
onMounted(() => {
  loadOfflineTickets();
  
  // Listen for online/offline events
  window.addEventListener('online', updateOnlineStatus);
  window.addEventListener('offline', updateOnlineStatus);
});
</script>

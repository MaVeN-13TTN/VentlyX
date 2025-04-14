<template>
  <div>
    <h3 class="font-semibold text-text-light dark:text-text-dark mb-3">Tickets</h3>
    <div v-if="loading" class="animate-pulse space-y-2">
      <div v-for="i in 2" :key="`loading-${i}`" class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
    </div>
    
    <div v-else-if="tickets.length > 0">
      <div 
        v-for="ticket in tickets" 
        :key="ticket.id" 
        class="mb-2 p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
        :class="{'border-amber-300 dark:border-amber-700 animate-pulse-once': ticket.recentlyUpdated}"
      >
        <div class="flex justify-between">
          <div>
            <h4 class="font-medium text-text-light dark:text-text-dark">{{ ticket.name }}</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ ticket.description }}</p>
            <div v-if="ticket.recentlyUpdated" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
              Recently updated!
            </div>
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
    
    <div class="mt-4 mb-2 flex items-center">
      <span class="text-sm text-gray-500 dark:text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Live updates enabled
      </span>
      <div class="ml-auto">
        <span 
          class="flex h-3 w-3" 
          :class="connectionStatus === 'connected' ? 'text-green-500' : connectionStatus === 'connecting' ? 'text-amber-500' : 'text-gray-500'"
        >
          <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full opacity-75 bg-current"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-current"></span>
        </span>
      </div>
    </div>
    
    <!-- CTA Button -->
    <button 
      @click="$emit('buyTickets')" 
      class="w-full py-3 px-6 bg-primary dark:bg-dark-primary text-white rounded-lg hover:bg-primary-dark dark:hover:bg-dark-primary-dark transition-colors flex items-center justify-center space-x-2 mt-4"
      :disabled="!hasAvailableTickets"
      :class="{ 'opacity-50 cursor-not-allowed': !hasAvailableTickets }"
    >
      <span>{{ hasAvailableTickets ? 'Buy Tickets' : 'Sold Out' }}</span>
      <svg v-if="hasAvailableTickets" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useNotificationStore } from '@/stores/notification';
import axios from 'axios';

const props = defineProps<{
  eventId: number;
}>();

const emit = defineEmits<{
  (e: 'buyTickets'): void;
  (e: 'update:tickets', tickets: any[]): void;
}>();

// Services and stores
const notificationStore = useNotificationStore();

// State
const tickets = ref<any[]>([]);
const loading = ref(true);
const connectionStatus = ref<'connecting' | 'connected' | 'disconnected'>('disconnected');
let wsConnection: WebSocket | null = null;
let reconnectInterval: number | null = null;
let updatedTicketTimers: Map<number, number> = new Map();

// Computed
const hasAvailableTickets = computed(() => {
  if (!tickets.value.length) return false;
  return tickets.value.some(ticket => ticket.available_quantity > 0);
});

// Methods
const fetchTickets = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/events/${props.eventId}/tickets`);
    tickets.value = response.data.data;
    emit('update:tickets', tickets.value);
  } catch (error) {
    console.error('Error fetching tickets:', error);
    notificationStore.error('Failed to load ticket information');
  } finally {
    loading.value = false;
  }
};

const connectWebSocket = () => {
  // Check if WebSocket is supported
  if (!('WebSocket' in window)) {
    console.warn('WebSockets are not supported in this browser');
    connectionStatus.value = 'disconnected';
    return;
  }

  // Close existing connection if any
  if (wsConnection) {
    wsConnection.close();
  }

  connectionStatus.value = 'connecting';
  
  try {
    // Connect to WebSocket server
    // Note: In a real implementation, you would get the WebSocket URL from your config
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const wsUrl = `${protocol}//${window.location.host}/ws/events/${props.eventId}/tickets`;
    
    wsConnection = new WebSocket(wsUrl);
    
    wsConnection.onopen = () => {
      connectionStatus.value = 'connected';
      console.info('WebSocket connection established');
      
      // Clear any reconnect interval
      if (reconnectInterval !== null) {
        clearInterval(reconnectInterval);
        reconnectInterval = null;
      }
    };
    
    wsConnection.onmessage = (event) => {
      try {
        // Parse ticket update data from the server
        const data = JSON.parse(event.data);
        
        if (data.type === 'ticket_update') {
          handleTicketUpdate(data.ticket);
        }
      } catch (err) {
        console.error('Error processing WebSocket message:', err);
      }
    };
    
    wsConnection.onerror = (error) => {
      console.error('WebSocket error:', error);
      connectionStatus.value = 'disconnected';
    };
    
    wsConnection.onclose = () => {
      connectionStatus.value = 'disconnected';
      console.info('WebSocket connection closed');
      
      // Try to reconnect after a delay
      if (reconnectInterval === null) {
        reconnectInterval = window.setInterval(() => {
          console.info('Attempting to reconnect WebSocket...');
          connectWebSocket();
        }, 5000); // Try every 5 seconds
      }
    };
  } catch (error) {
    console.error('Failed to establish WebSocket connection:', error);
    connectionStatus.value = 'disconnected';
  }
};

const handleTicketUpdate = (updatedTicket: any) => {
  // Find the ticket in our array
  const index = tickets.value.findIndex(t => t.id === updatedTicket.id);
  
  if (index !== -1) {
    const currentTicket = tickets.value[index];
    // Only highlight if quantity changed
    const quantityChanged = currentTicket.available_quantity !== updatedTicket.available_quantity;
    
    // Update ticket data
    tickets.value[index] = {
      ...updatedTicket,
      recentlyUpdated: quantityChanged
    };
    
    // If quantity changed, show notification
    if (quantityChanged) {
      const message = updatedTicket.available_quantity === 0
        ? `${updatedTicket.name} tickets just sold out!`
        : updatedTicket.available_quantity < 5
        ? `Only ${updatedTicket.available_quantity} ${updatedTicket.name} tickets left!`
        : `${updatedTicket.name} tickets availability updated`;
        
      notificationStore.info(message);
      
      // Clear previous timer for this ticket if it exists
      if (updatedTicketTimers.has(updatedTicket.id)) {
        clearTimeout(updatedTicketTimers.get(updatedTicket.id));
      }
      
      // Set timer to remove highlight after 5 seconds
      const timerId = window.setTimeout(() => {
        const idx = tickets.value.findIndex(t => t.id === updatedTicket.id);
        if (idx !== -1) {
          tickets.value[idx] = {
            ...tickets.value[idx],
            recentlyUpdated: false
          };
        }
        updatedTicketTimers.delete(updatedTicket.id);
      }, 5000);
      
      updatedTicketTimers.set(updatedTicket.id, timerId);
    }
    
    // Emit the updated tickets
    emit('update:tickets', [...tickets.value]);
  }
};

// For testing purposes - simulate ticket updates in environments without WebSockets
const setupSimulatedUpdates = () => {
  if (import.meta.env.DEV && connectionStatus.value === 'disconnected') {
    console.info('Setting up simulated ticket updates for development environment');
    
    // Simulate connecting
    connectionStatus.value = 'connecting';
    setTimeout(() => {
      connectionStatus.value = 'connected';
    }, 1500);
    
    // Simulate occasional ticket updates
    const simulationInterval = setInterval(() => {
      if (!tickets.value.length) return;
      
      const randomTicketIndex = Math.floor(Math.random() * tickets.value.length);
      const ticket = {...tickets.value[randomTicketIndex]};
      
      // Randomly update available quantity
      const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
      ticket.available_quantity = Math.max(0, Math.min(20, ticket.available_quantity + change));
      
      handleTicketUpdate(ticket);
    }, 15000); // Simulate updates every 15 seconds
    
    return simulationInterval;
  }
  return null;
};

// Clean up function
const cleanUp = () => {
  // Close WebSocket connection
  if (wsConnection) {
    wsConnection.close();
    wsConnection = null;
  }
  
  // Clear reconnect interval
  if (reconnectInterval !== null) {
    clearInterval(reconnectInterval);
    reconnectInterval = null;
  }
  
  // Clear all highlight timers
  updatedTicketTimers.forEach((timerId) => {
    clearTimeout(timerId);
  });
  updatedTicketTimers.clear();
};

// Lifecycle hooks
let simulationInterval: number | null = null;

onMounted(async () => {
  await fetchTickets();
  connectWebSocket();
  simulationInterval = setupSimulatedUpdates();
});

onUnmounted(() => {
  cleanUp();
  if (simulationInterval !== null) {
    clearInterval(simulationInterval);
  }
});

// Watch for event ID changes
watch(() => props.eventId, async () => {
  cleanUp();
  await fetchTickets();
  connectWebSocket();
}, { immediate: false });
</script>

<style scoped>
@keyframes pulse-once {
  0%, 100% {
    background-color: inherit;
  }
  50% {
    background-color: rgba(251, 191, 36, 0.1);
  }
}

.animate-pulse-once {
  animation: pulse-once 2s ease-in-out;
}
</style>
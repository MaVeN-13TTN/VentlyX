<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-text-light dark:text-text-dark">
          Book Tickets
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          {{ event ? event.title : 'Loading event details...' }}
        </p>

        <!-- Session Timer -->
        <div v-if="sessionActive" class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 dark:text-amber-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
              </svg>
              <span class="text-amber-800 dark:text-amber-300 text-sm">
                Your tickets are reserved for <strong>{{ formatTime(sessionTimeRemaining) }}</strong>
              </span>
            </div>

            <!-- Visual Timer -->
            <div class="w-24 h-6 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div
                class="h-full bg-amber-500 dark:bg-amber-400 transition-all duration-1000 ease-linear"
                :style="{ width: `${(sessionTimeRemaining / SESSION_DURATION) * 100}%` }"
              ></div>
            </div>
          </div>

          <!-- Warning when time is running low -->
          <div v-if="sessionTimeRemaining < 60" class="mt-2 text-sm text-red-600 dark:text-red-400 animate-pulse">
            <strong>Warning:</strong> Your reservation is about to expire! Complete your booking now.
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Ticket Selection -->
        <div class="lg:col-span-2 space-y-6">
          <div v-if="loading" class="animate-pulse space-y-4">
            <div class="h-12 bg-gray-200 dark:bg-gray-700 rounded-lg w-1/3"></div>
            <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
            <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
          </div>

          <div v-else>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
              <h2 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">
                Select Tickets
              </h2>

              <div v-if="ticketTypes.length === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No tickets available for this event.</p>
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="ticket in ticketTypes"
                  :key="ticket.id"
                  class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 transition-all"
                  :class="{'border-primary dark:border-primary-400': selectedTickets[ticket.id]?.quantity > 0}"
                >
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                    <div class="mb-3 sm:mb-0">
                      <h3 class="font-medium text-text-light dark:text-text-dark">{{ ticket.name }}</h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ ticket.description }}</p>
                      <p class="text-primary dark:text-primary-400 font-medium mt-1">${{ ticket.price.toFixed(2) }}</p>

                      <div class="mt-2 text-sm">
                        <span
                          :class="[
                            'px-2 py-0.5 rounded-full text-xs',
                            ticket.available_quantity > 10
                              ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                              : ticket.available_quantity > 0
                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'
                                : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                          ]"
                        >
                          {{ getAvailabilityText(ticket) }}
                        </span>
                      </div>
                    </div>

                    <div class="flex items-center">
                      <button
                        @click="decrementQuantity(ticket)"
                        class="w-8 h-8 rounded-full flex items-center justify-center border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                        :disabled="!selectedTickets[ticket.id] || selectedTickets[ticket.id].quantity <= 0"
                        :class="{'opacity-50 cursor-not-allowed': !selectedTickets[ticket.id] || selectedTickets[ticket.id].quantity <= 0}"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>

                      <span class="w-10 text-center text-text-light dark:text-text-dark">
                        {{ selectedTickets[ticket.id]?.quantity || 0 }}
                      </span>

                      <button
                        @click="incrementQuantity(ticket)"
                        class="w-8 h-8 rounded-full flex items-center justify-center border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                        :disabled="!canIncrementQuantity(ticket)"
                        :class="{'opacity-50 cursor-not-allowed': !canIncrementQuantity(ticket)}"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <div v-if="getTicketError(ticket.id)" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ getTicketError(ticket.id) }}
                  </div>
                </div>
              </div>
            </div>

            <div v-if="event" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mt-6">
              <h2 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">
                Event Details
              </h2>

              <div class="space-y-4">
                <div>
                  <h3 class="font-medium text-text-light dark:text-text-dark">Date & Time</h3>
                  <p class="text-gray-600 dark:text-gray-400">{{ formatDateTime(event.start_time) }}</p>
                </div>

                <div>
                  <h3 class="font-medium text-text-light dark:text-text-dark">Location</h3>
                  <p class="text-gray-600 dark:text-gray-400">{{ event.location }}</p>
                </div>

                <div>
                  <h3 class="font-medium text-text-light dark:text-text-dark">Organizer</h3>
                  <p class="text-gray-600 dark:text-gray-400">{{ event.organizer?.name || 'Unknown' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 sticky top-6">
            <h2 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">
              Order Summary
            </h2>

            <div v-if="loading" class="animate-pulse space-y-4">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
              <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded mt-6"></div>
            </div>

            <div v-else>
              <div v-if="totalQuantity === 0" class="text-center py-6">
                <p class="text-gray-500 dark:text-gray-400">Select tickets to see your order summary.</p>
              </div>

              <div v-else>
                <div class="space-y-3 mb-6">
                  <div v-for="(item, ticketId) in selectedTickets" :key="ticketId" v-if="item.quantity > 0">
                    <div class="flex justify-between">
                      <div>
                        <span class="text-text-light dark:text-text-dark">{{ item.name }}</span>
                        <span class="text-gray-500 dark:text-gray-400 ml-1">× {{ item.quantity }}</span>
                      </div>
                      <span class="text-text-light dark:text-text-dark">${{ (item.price * item.quantity).toFixed(2) }}</span>
                    </div>
                  </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-6">
                  <div class="flex justify-between font-medium">
                    <span class="text-text-light dark:text-text-dark">Total</span>
                    <span class="text-text-light dark:text-text-dark">${{ totalPrice.toFixed(2) }}</span>
                  </div>
                </div>

                <button
                  @click="proceedToCheckout"
                  class="w-full py-3 px-6 bg-primary dark:bg-primary-600 text-white rounded-lg hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors flex items-center justify-center space-x-2"
                  :disabled="!canProceed || isProcessing"
                  :class="{'opacity-50 cursor-not-allowed': !canProceed || isProcessing}"
                >
                  <span v-if="isProcessing">Processing...</span>
                  <span v-else>Proceed to Checkout</span>
                  <svg v-if="!isProcessing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/notification';
import BookingService from '@/services/api/BookingService';
import type { TicketType } from '@/services/api/BookingService';
import axios from 'axios';

// Services and stores
const route = useRoute();
const router = useRouter();
const notificationStore = useNotificationStore();
const bookingService = BookingService.getInstance();

// State
const event = ref<any>(null);
const ticketTypes = ref<TicketType[]>([]);
const selectedTickets = ref<Record<number, { id: number, name: string, price: number, quantity: number }>>({});
const loading = ref(true);
const isProcessing = ref(false);
const errors = ref<Record<number, string>>({});

// Session timer state
const sessionActive = ref(false);
const sessionTimeRemaining = ref(0);
const sessionTimerId = ref<number | null>(null);
const SESSION_DURATION = 10 * 60; // 10 minutes in seconds

// Computed
const totalQuantity = computed(() => {
  return Object.values(selectedTickets.value).reduce((sum, item) => sum + item.quantity, 0);
});

const totalPrice = computed(() => {
  return Object.values(selectedTickets.value).reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const canProceed = computed(() => {
  return totalQuantity.value > 0 && Object.keys(errors.value).length === 0;
});

// Methods
const fetchEvent = async () => {
  try {
    const response = await axios.get(`/api/events/${route.params.id}`);
    event.value = response.data.data;
  } catch (error) {
    console.error('Error fetching event:', error);
    notificationStore.error('Failed to load event details');
  }
};

const fetchTicketTypes = async () => {
  try {
    ticketTypes.value = await bookingService.getTicketTypes(Number(route.params.id));

    // Initialize selected tickets
    ticketTypes.value.forEach(ticket => {
      selectedTickets.value[ticket.id] = {
        id: ticket.id,
        name: ticket.name,
        price: ticket.price,
        quantity: 0
      };
    });
  } catch (error) {
    console.error('Error fetching ticket types:', error);
    notificationStore.error('Failed to load ticket information');
  } finally {
    loading.value = false;
  }
};

const getAvailabilityText = (ticket: TicketType) => {
  if (ticket.available_quantity === 0) {
    return 'Sold Out';
  } else if (ticket.available_quantity < 10) {
    return `Only ${ticket.available_quantity} left`;
  } else {
    return 'Available';
  }
};

const incrementQuantity = (ticket: TicketType) => {
  if (!canIncrementQuantity(ticket)) return;

  selectedTickets.value[ticket.id].quantity++;
  validateTicketQuantity(ticket.id);

  // Start session timer if not already started
  if (!sessionActive.value) {
    startSessionTimer();
  }
};

const decrementQuantity = (ticket: TicketType) => {
  if (selectedTickets.value[ticket.id].quantity > 0) {
    selectedTickets.value[ticket.id].quantity--;
    validateTicketQuantity(ticket.id);
  }

  // If no tickets selected, clear session timer
  if (totalQuantity.value === 0) {
    clearSessionTimer();
  }
};

const canIncrementQuantity = (ticket: TicketType) => {
  const currentQuantity = selectedTickets.value[ticket.id]?.quantity || 0;

  // Check if we've reached the maximum allowed per order
  if (currentQuantity >= ticket.max_per_order) {
    return false;
  }

  // Check if we've reached the available quantity
  if (currentQuantity >= ticket.available_quantity) {
    return false;
  }

  return true;
};

const validateTicketQuantity = (ticketId: number) => {
  const ticket = ticketTypes.value.find(t => t.id === ticketId);
  const selected = selectedTickets.value[ticketId];

  if (!ticket || !selected) return;

  // Clear previous error
  delete errors.value[ticketId];

  // Validate quantity
  if (selected.quantity > ticket.available_quantity) {
    errors.value[ticketId] = `Only ${ticket.available_quantity} tickets available`;
  } else if (selected.quantity > ticket.max_per_order) {
    errors.value[ticketId] = `Maximum ${ticket.max_per_order} tickets per order`;
  }
};

const getTicketError = (ticketId: number) => {
  return errors.value[ticketId];
};

const proceedToCheckout = async () => {
  if (!canProceed.value || isProcessing.value) return;

  isProcessing.value = true;

  try {
    // Find the first selected ticket type
    const firstSelectedTicket = Object.values(selectedTickets.value).find(ticket => ticket.quantity > 0);

    if (!firstSelectedTicket) {
      notificationStore.error('Please select at least one ticket');
      return;
    }

    // Create booking
    const booking = await bookingService.createBooking({
      event_id: Number(route.params.id),
      ticket_type_id: firstSelectedTicket.id,
      quantity: firstSelectedTicket.quantity
    });

    // Navigate to checkout with booking ID
    router.push({
      name: 'event-checkout',
      params: { id: route.params.id },
      query: { booking_id: booking.id.toString() }
    });
  } catch (error: any) {
    console.error('Error creating booking:', error);
    notificationStore.error(error.message || 'Failed to create booking');
  } finally {
    isProcessing.value = false;
  }
};

// Session timer functions
const startSessionTimer = () => {
  sessionActive.value = true;
  sessionTimeRemaining.value = SESSION_DURATION;

  // Clear any existing timer
  if (sessionTimerId.value) {
    clearInterval(sessionTimerId.value);
  }

  // Start countdown
  sessionTimerId.value = setInterval(() => {
    if (sessionTimeRemaining.value > 0) {
      sessionTimeRemaining.value--;

      // Show warning notification when time is running low (30 seconds left)
      if (sessionTimeRemaining.value === 30) {
        notificationStore.warning('Your ticket reservation will expire in 30 seconds!');
      }
    } else {
      clearSessionTimer(true); // Show expired notification
    }
  }, 1000);
};

const clearSessionTimer = (showExpiredNotification = false) => {
  if (sessionTimerId.value) {
    clearInterval(sessionTimerId.value);
    sessionTimerId.value = null;
  }
  sessionActive.value = false;

  if (showExpiredNotification) {
    notificationStore.warning('Your ticket reservation has expired. The tickets have been released.');

    // Reset selected tickets
    Object.keys(selectedTickets.value).forEach(key => {
      selectedTickets.value[Number(key)].quantity = 0;
    });

    // Refresh ticket types to get updated availability
    fetchTicketTypes();
  }
};

const formatTime = (seconds: number) => {
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

const formatDateTime = (dateString: string) => {
  if (!dateString) return '';

  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  });
};

// Lifecycle hooks
onMounted(async () => {
  await Promise.all([fetchEvent(), fetchTicketTypes()]);
});

onBeforeUnmount(() => {
  clearSessionTimer();
});

// Watch for route changes
watch(() => route.params.id, async () => {
  loading.value = true;
  clearSessionTimer();
  await Promise.all([fetchEvent(), fetchTicketTypes()]);
}, { immediate: false });
</script>

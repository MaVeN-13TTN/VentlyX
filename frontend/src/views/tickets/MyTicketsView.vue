<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-text-light dark:text-text-dark">
          My Tickets
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Manage your bookings and tickets
        </p>
      </div>

      <!-- Filters -->
      <div class="mb-8 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex space-x-2">
            <button
              @click="activeFilter = 'all'"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="[
                activeFilter === 'all'
                  ? 'bg-primary dark:bg-primary-600 text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-200 dark:hover:bg-gray-600'
              ]"
            >
              All
            </button>

            <button
              @click="activeFilter = 'upcoming'"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="[
                activeFilter === 'upcoming'
                  ? 'bg-primary dark:bg-primary-600 text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-200 dark:hover:bg-gray-600'
              ]"
            >
              Upcoming
            </button>

            <button
              @click="activeFilter = 'past'"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="[
                activeFilter === 'past'
                  ? 'bg-primary dark:bg-primary-600 text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-200 dark:hover:bg-gray-600'
              ]"
            >
              Past
            </button>
          </div>

          <div class="relative">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search bookings..."
              class="w-full sm:w-64 px-4 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-text-light dark:text-text-dark"
            />
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Offline Notice -->
      <div v-if="!isOnline" class="mb-8 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
        <div class="flex items-start">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 dark:text-amber-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="text-amber-800 dark:text-amber-300 font-medium">You're currently offline</h3>
            <p class="text-amber-700 dark:text-amber-400 text-sm mt-1">
              Your saved tickets are still available below. Some features may be limited until you reconnect.
            </p>
          </div>
        </div>
      </div>

      <!-- Offline Tickets -->
      <div v-if="hasOfflineTickets" class="mb-8">
        <OfflineTicketsView />
      </div>

      <!-- Tickets List -->
      <div v-if="loading" class="animate-pulse space-y-6">
        <div v-for="i in 3" :key="`loading-${i}`" class="h-40 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
      </div>

      <div v-else-if="filteredBookings.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
        </svg>

        <h2 class="text-xl font-semibold text-text-light dark:text-text-dark mb-2">No tickets found</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          {{
            activeFilter === 'all'
              ? "You don't have any tickets yet."
              : activeFilter === 'upcoming'
                ? "You don't have any upcoming tickets."
                : "You don't have any past tickets."
          }}
        </p>

        <router-link
          :to="{ name: 'events' }"
          class="py-2 px-6 bg-primary dark:bg-primary-600 text-white rounded-lg hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors inline-block"
        >
          Browse Events
        </router-link>
      </div>

      <div v-else class="space-y-6">
        <div
          v-for="booking in filteredBookings"
          :key="booking.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden"
        >
          <div class="grid grid-cols-1 md:grid-cols-4">
            <!-- Event Image -->
            <div class="md:col-span-1 h-40 md:h-full bg-gray-200 dark:bg-gray-700 relative">
              <img
                v-if="booking.event?.image_url"
                :src="booking.event.image_url"
                :alt="booking.event.title"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>

              <!-- Status Badge -->
              <div
                class="absolute top-2 left-2 px-2 py-1 rounded-full text-xs font-medium"
                :class="getStatusClass(booking.status)"
              >
                {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
              </div>
            </div>

            <!-- Booking Details -->
            <div class="md:col-span-2 p-6">
              <h2 class="text-lg font-semibold text-text-light dark:text-text-dark mb-2">
                {{ booking.event?.title || 'Unknown Event' }}
              </h2>

              <div class="space-y-2 text-sm">
                <div class="flex items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span class="text-gray-600 dark:text-gray-400">
                    {{ formatDateTime(booking.event?.start_time) }}
                  </span>
                </div>

                <div class="flex items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="text-gray-600 dark:text-gray-400">
                    {{ booking.event?.location || 'Location not specified' }}
                  </span>
                </div>

                <div class="flex items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                  </svg>
                  <span class="text-gray-600 dark:text-gray-400">
                    {{ booking.quantity }} × {{ booking.ticket_type?.name || 'Standard Ticket' }}
                  </span>
                </div>

                <div class="flex items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  <span class="text-gray-600 dark:text-gray-400">
                    Ref: {{ booking.booking_reference }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="md:col-span-1 p-6 bg-gray-50 dark:bg-gray-750 flex flex-col justify-center space-y-3">
              <router-link
                :to="{ name: 'ticket-details', params: { id: booking.id } }"
                class="w-full py-2 px-4 bg-primary dark:bg-primary-600 text-white rounded-lg hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors text-center"
              >
                View Ticket
              </router-link>

              <button
                v-if="canTransfer(booking)"
                @click="openTransferModal(booking)"
                class="w-full py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                Transfer
              </button>

              <button
                v-if="canCancel(booking)"
                @click="openCancelModal(booking)"
                class="w-full py-2 px-4 border border-red-300 dark:border-red-800 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="mt-8 flex justify-center">
        <div class="flex space-x-2">
          <button
            @click="changePage(currentPage - 1)"
            class="px-3 py-1 rounded-lg border border-gray-300 dark:border-gray-600 text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :disabled="currentPage === 1"
            :class="{'opacity-50 cursor-not-allowed': currentPage === 1}"
          >
            Previous
          </button>

          <button
            v-for="page in paginationRange"
            :key="page"
            @click="changePage(page)"
            class="px-3 py-1 rounded-lg border transition-colors"
            :class="[
              currentPage === page
                ? 'bg-primary dark:bg-primary-600 text-white border-primary dark:border-primary-600'
                : 'border-gray-300 dark:border-gray-600 text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </button>

          <button
            @click="changePage(currentPage + 1)"
            class="px-3 py-1 rounded-lg border border-gray-300 dark:border-gray-600 text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :disabled="currentPage === totalPages"
            :class="{'opacity-50 cursor-not-allowed': currentPage === totalPages}"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Transfer Modal -->
    <div v-if="showTransferModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <h2 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">
          Transfer Ticket
        </h2>

        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Enter the email address of the person you want to transfer this ticket to.
        </p>

        <div class="form-group mb-4">
          <label for="transfer-email" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Email Address</label>
          <input
            type="email"
            id="transfer-email"
            v-model="transferEmail"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-text-light dark:text-text-dark"
            placeholder="email@example.com"
          />
          <p v-if="transferError" class="text-sm text-red-600 dark:text-red-400 mt-1">{{ transferError }}</p>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            @click="closeTransferModal"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            Cancel
          </button>

          <button
            @click="transferTicket"
            class="px-4 py-2 bg-primary dark:bg-primary-600 text-white rounded-lg hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors"
            :disabled="isProcessing"
            :class="{'opacity-50 cursor-not-allowed': isProcessing}"
          >
            {{ isProcessing ? 'Processing...' : 'Transfer' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Cancel Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <h2 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">
          Cancel Booking
        </h2>

        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Are you sure you want to cancel this booking? This action cannot be undone.
        </p>

        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
          <p class="text-sm text-red-600 dark:text-red-400">
            <span class="font-medium">Note:</span> Refund policies may apply. Please check the event's refund policy for details.
          </p>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            @click="closeCancelModal"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            Keep Booking
          </button>

          <button
            @click="cancelBooking"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            :disabled="isProcessing"
            :class="{'opacity-50 cursor-not-allowed': isProcessing}"
          >
            {{ isProcessing ? 'Processing...' : 'Cancel Booking' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useNotificationStore } from '@/stores/notification';
import BookingService from '@/services/api/BookingService';
import TicketService from '@/services/api/TicketService';
import type { Booking } from '@/services/api/BookingService';
import OfflineTicketsView from '@/components/tickets/OfflineTicketsView.vue';

// Services and stores
const notificationStore = useNotificationStore();
const bookingService = BookingService.getInstance();
const ticketService = TicketService.getInstance();

// State
const bookings = ref<Booking[]>([]);
const loading = ref(true);
const activeFilter = ref<'all' | 'upcoming' | 'past'>('all');
const searchQuery = ref('');
const currentPage = ref(1);
const totalPages = ref(1);
const itemsPerPage = 10;
const hasOfflineTickets = ref(false);
const isOnline = ref(navigator.onLine);

// Modal state
const showTransferModal = ref(false);
const showCancelModal = ref(false);
const selectedBooking = ref<Booking | null>(null);
const transferEmail = ref('');
const transferError = ref('');
const isProcessing = ref(false);

// Computed
const filteredBookings = computed(() => {
  let filtered = [...bookings.value];

  // Apply filter
  if (activeFilter.value === 'upcoming') {
    filtered = filtered.filter(booking => {
      if (!booking.event?.start_time) return false;
      return new Date(booking.event.start_time) > new Date();
    });
  } else if (activeFilter.value === 'past') {
    filtered = filtered.filter(booking => {
      if (!booking.event?.start_time) return false;
      return new Date(booking.event.start_time) < new Date();
    });
  }

  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(booking =>
      booking.event?.title?.toLowerCase().includes(query) ||
      booking.booking_reference?.toLowerCase().includes(query) ||
      booking.ticket_type?.name?.toLowerCase().includes(query) ||
      booking.event?.location?.toLowerCase().includes(query)
    );
  }

  return filtered;
});

const paginationRange = computed(() => {
  const range = [];
  const maxVisiblePages = 5;

  if (totalPages.value <= maxVisiblePages) {
    // Show all pages
    for (let i = 1; i <= totalPages.value; i++) {
      range.push(i);
    }
  } else {
    // Show a subset of pages
    let start = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2));
    let end = Math.min(totalPages.value, start + maxVisiblePages - 1);

    // Adjust if we're near the end
    if (end === totalPages.value) {
      start = Math.max(1, end - maxVisiblePages + 1);
    }

    for (let i = start; i <= end; i++) {
      range.push(i);
    }
  }

  return range;
});

// Methods
const fetchBookings = async () => {
  try {
    loading.value = true;
    const response = await bookingService.getUserBookings({
      page: currentPage.value
    });

    bookings.value = response.data;
    totalPages.value = Math.ceil(response.meta.total / itemsPerPage);
  } catch (error) {
    console.error('Error fetching bookings:', error);
    notificationStore.error('Failed to load your bookings');
  } finally {
    loading.value = false;
  }
};

const changePage = (page: number) => {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
};

const getStatusClass = (status: string) => {
  switch (status) {
    case 'confirmed':
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
    case 'pending':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
    case 'cancelled':
      return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
    case 'refunded':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
  }
};

const formatDateTime = (dateString?: string) => {
  if (!dateString) return 'Date not specified';

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

const canTransfer = (booking: Booking) => {
  if (!booking.event?.start_time) return false;

  // Can transfer if:
  // 1. Booking is confirmed
  // 2. Event hasn't started yet
  // 3. Not already transferred
  return (
    booking.status === 'confirmed' &&
    new Date(booking.event.start_time) > new Date() &&
    !booking.transferred_to
  );
};

const canCancel = (booking: Booking) => {
  if (!booking.event?.start_time) return false;

  // Can cancel if:
  // 1. Booking is confirmed or pending
  // 2. Event hasn't started yet
  return (
    (booking.status === 'confirmed' || booking.status === 'pending') &&
    new Date(booking.event.start_time) > new Date()
  );
};

const openTransferModal = (booking: Booking) => {
  selectedBooking.value = booking;
  transferEmail.value = '';
  transferError.value = '';
  showTransferModal.value = true;
};

const closeTransferModal = () => {
  showTransferModal.value = false;
  selectedBooking.value = null;
};

const transferTicket = async () => {
  if (!selectedBooking.value || isProcessing.value) return;

  // Validate email
  if (!transferEmail.value) {
    transferError.value = 'Please enter an email address';
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(transferEmail.value)) {
    transferError.value = 'Please enter a valid email address';
    return;
  }

  isProcessing.value = true;

  try {
    await bookingService.transferBooking(selectedBooking.value.id, transferEmail.value);

    notificationStore.success('Ticket transfer initiated successfully');
    closeTransferModal();
    fetchBookings();
  } catch (err: any) {
    console.error('Error transferring ticket:', err);
    transferError.value = err.message || 'Failed to transfer ticket';
  } finally {
    isProcessing.value = false;
  }
};

const openCancelModal = (booking: Booking) => {
  selectedBooking.value = booking;
  showCancelModal.value = true;
};

const closeCancelModal = () => {
  showCancelModal.value = false;
  selectedBooking.value = null;
};

const cancelBooking = async () => {
  if (!selectedBooking.value || isProcessing.value) return;

  isProcessing.value = true;

  try {
    await bookingService.cancelBooking(selectedBooking.value.id);

    notificationStore.success('Booking cancelled successfully');
    closeCancelModal();
    fetchBookings();
  } catch (err: any) {
    console.error('Error cancelling booking:', err);
    notificationStore.error(err.message || 'Failed to cancel booking');
  } finally {
    isProcessing.value = false;
  }
};

// Check for offline tickets
const checkOfflineTickets = () => {
  hasOfflineTickets.value = ticketService.hasOfflineTickets();
};

// Update online status
const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine;
};

// Lifecycle hooks
onMounted(async () => {
  await fetchBookings();
  checkOfflineTickets();

  // Listen for online/offline events
  window.addEventListener('online', updateOnlineStatus);
  window.addEventListener('offline', updateOnlineStatus);
});

// Clean up event listeners when component is unmounted
onUnmounted(() => {
  window.removeEventListener('online', updateOnlineStatus);
  window.removeEventListener('offline', updateOnlineStatus);
});

// Watch for filter changes
watch([activeFilter, searchQuery], () => {
  currentPage.value = 1;
});

// Watch for page changes
watch(currentPage, async () => {
  await fetchBookings();
});
</script>

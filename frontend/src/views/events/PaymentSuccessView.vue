<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
        <div class="text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-green-500 dark:text-green-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          
          <h1 class="text-3xl font-bold text-text-light dark:text-text-dark mb-4">
            Payment Successful!
          </h1>
          
          <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
            Your booking has been confirmed and your tickets are ready. We've sent a confirmation email with all the details.
          </p>
          
          <div v-if="loading" class="animate-pulse space-y-4 max-w-md mx-auto mb-8">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mx-auto"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mx-auto"></div>
          </div>
          
          <div v-else-if="booking" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-8 max-w-md mx-auto text-left">
            <h2 class="font-semibold text-text-light dark:text-text-dark mb-4">Booking Details</h2>
            
            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Event:</span>
                <span class="text-text-light dark:text-text-dark font-medium">{{ booking.event.title }}</span>
              </div>
              
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Date:</span>
                <span class="text-text-light dark:text-text-dark font-medium">{{ formatDate(booking.event.start_time) }}</span>
              </div>
              
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Tickets:</span>
                <span class="text-text-light dark:text-text-dark font-medium">{{ booking.quantity }} × {{ booking.ticket_type.name }}</span>
              </div>
              
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Total:</span>
                <span class="text-text-light dark:text-text-dark font-medium">${{ booking.total_price.toFixed(2) }}</span>
              </div>
              
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Reference:</span>
                <span class="text-text-light dark:text-text-dark font-medium font-mono">{{ booking.booking_reference }}</span>
              </div>
            </div>
          </div>
          
          <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
            <router-link 
              :to="{ name: 'my-tickets' }"
              class="py-3 px-6 bg-primary dark:bg-primary-600 text-white rounded-lg hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors"
            >
              View My Tickets
            </router-link>
            
            <router-link 
              :to="{ name: 'events' }"
              class="py-3 px-6 border border-gray-300 dark:border-gray-600 rounded-lg text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Browse More Events
            </router-link>
          </div>
        </div>
      </div>
      
      <div class="mt-8 text-center">
        <h2 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">
          What's Next?
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-primary dark:text-primary-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <h3 class="font-medium text-text-light dark:text-text-dark mb-2">Check Your Email</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">We've sent a confirmation email with your tickets and receipt.</p>
          </div>
          
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-primary dark:text-primary-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
            <h3 class="font-medium text-text-light dark:text-text-dark mb-2">Save Your Tickets</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Access your tickets anytime from your account dashboard.</p>
          </div>
          
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-primary dark:text-primary-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="font-medium text-text-light dark:text-text-dark mb-2">Mark Your Calendar</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Don't forget to add this event to your calendar so you don't miss it!</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useNotificationStore } from '@/stores/notification';
import BookingService from '@/services/api/BookingService';
import type { Booking } from '@/services/api/BookingService';

// Services and stores
const route = useRoute();
const notificationStore = useNotificationStore();
const bookingService = BookingService.getInstance();

// State
const loading = ref(true);
const booking = ref<Booking | null>(null);

// Methods
const fetchBooking = async () => {
  try {
    const bookingId = route.query.booking_id;
    
    if (!bookingId) {
      notificationStore.error('No booking ID provided');
      return;
    }
    
    booking.value = await bookingService.getBooking(Number(bookingId));
  } catch (err: any) {
    console.error('Error fetching booking:', err);
    notificationStore.error(err.message || 'Failed to load booking details');
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
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
  await fetchBooking();
});
</script>

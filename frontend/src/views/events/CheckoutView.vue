<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-text-light dark:text-text-dark">
          Checkout
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Complete your booking
        </p>
      </div>

      <!-- Checkout Steps -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div
            v-for="(step, index) in steps"
            :key="step.id"
            class="flex-1 relative"
          >
            <div class="flex items-center">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                :class="[
                  currentStep >= index
                    ? 'bg-primary dark:bg-primary-600 text-white'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                ]"
              >
                <span v-if="currentStep > index">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </span>
                <span v-else>{{ index + 1 }}</span>
              </div>
              <div
                v-if="index < steps.length - 1"
                class="flex-1 h-0.5 mx-2"
                :class="[
                  currentStep > index
                    ? 'bg-primary dark:bg-primary-600'
                    : 'bg-gray-200 dark:bg-gray-700'
                ]"
              ></div>
            </div>
            <div class="text-center mt-2">
              <span
                class="text-sm font-medium"
                :class="[
                  currentStep >= index
                    ? 'text-text-light dark:text-text-dark'
                    : 'text-gray-500 dark:text-gray-400'
                ]"
              >
                {{ step.name }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary dark:border-primary-400"></div>
      </div>

      <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-red-500 dark:text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h2 class="text-xl font-semibold text-red-800 dark:text-red-300 mb-2">{{ error }}</h2>
        <p class="text-red-600 dark:text-red-400 mb-4">Please try again or contact support if the problem persists.</p>
        <button
          @click="goBack"
          class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
        >
          Go Back
        </button>
      </div>

      <div v-else-if="booking" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
          <!-- Review Step -->
          <div v-if="currentStep === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">
              Review Your Order
            </h2>

            <div class="space-y-6">
              <div>
                <h3 class="font-medium text-text-light dark:text-text-dark mb-2">Event Details</h3>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                  <p class="font-medium text-text-light dark:text-text-dark">{{ booking.event?.title }}</p>
                  <p class="text-gray-600 dark:text-gray-400 text-sm">{{ formatDateTime(booking.event?.start_time) }}</p>
                  <p class="text-gray-600 dark:text-gray-400 text-sm">{{ booking.event?.location }}</p>
                </div>
              </div>

              <div>
                <h3 class="font-medium text-text-light dark:text-text-dark mb-2">Ticket Details</h3>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                  <div class="flex justify-between items-center">
                    <div>
                      <p class="font-medium text-text-light dark:text-text-dark">{{ booking.ticket_type?.name }}</p>
                      <p class="text-gray-600 dark:text-gray-400 text-sm">Quantity: {{ booking.quantity }}</p>
                    </div>
                    <p class="font-medium text-text-light dark:text-text-dark">${{ ((booking.ticket_type?.price ?? 0) * booking.quantity).toFixed(2) }}</p>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="font-medium text-text-light dark:text-text-dark mb-2">Booking Reference</h3>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                  <p class="font-mono text-text-light dark:text-text-dark">{{ booking.booking_reference }}</p>
                </div>
              </div>
            </div>

            <div class="mt-8 flex justify-end">
              <button
                @click="nextStep"
                class="py-2 px-6 bg-primary dark:bg-primary-600 text-white rounded-lg hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors"
              >
                Continue to Payment
              </button>
            </div>
          </div>

          <!-- Payment Step -->
          <div v-if="currentStep === 1" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 relative">
            <!-- Payment Status Animation Container -->
            <div v-if="paymentStatus !== 'idle'" class="absolute inset-0 bg-white/90 dark:bg-gray-800/90 z-10 flex items-center justify-center">
              <!-- Processing Animation -->
              <div v-if="paymentStatus === 'processing'" class="text-center">
                <div class="inline-block w-16 h-16 border-4 border-primary dark:border-primary-400 border-t-transparent dark:border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-lg font-medium text-text-light dark:text-text-dark">Processing your payment...</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Please don't close this window</p>
              </div>

              <!-- Success Animation -->
              <div v-if="paymentStatus === 'success'" class="text-center">
                <div class="success-checkmark">
                  <div class="check-icon">
                    <span class="icon-line line-tip"></span>
                    <span class="icon-line line-long"></span>
                    <div class="icon-circle"></div>
                    <div class="icon-fix"></div>
                  </div>
                </div>
                <p class="text-lg font-medium text-text-light dark:text-text-dark mt-4">Payment Successful!</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Redirecting to confirmation...</p>
              </div>

              <!-- Error Animation -->
              <div v-if="paymentStatus === 'error'" class="text-center">
                <div class="error-x-mark">
                  <div class="x-icon">
                    <span class="icon-line line-left"></span>
                    <span class="icon-line line-right"></span>
                  </div>
                </div>
                <p class="text-lg font-medium text-red-600 dark:text-red-400 mt-4">Payment Failed</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Please try again or use a different payment method</p>
              </div>
            </div>
            <h2 class="text-xl font-semibold text-text-light dark:text-text-dark mb-4">
              Payment Method
            </h2>

            <div class="space-y-6">
              <div>
                <div class="flex space-x-4 mb-6">
                  <button
                    @click="paymentMethod = 'stripe'"
                    class="flex-1 py-3 px-4 rounded-lg border-2 transition-colors flex items-center justify-center space-x-2"
                    :class="[
                      paymentMethod === 'stripe'
                        ? 'border-primary dark:border-primary-500 bg-primary/5 dark:bg-primary-900/10'
                        : 'border-gray-200 dark:border-gray-700'
                    ]"
                  >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M2 9C2 7.89543 2.89543 7 4 7H20C21.1046 7 22 7.89543 22 9V18C22 19.1046 21.1046 20 20 20H4C2.89543 20 2 19.1046 2 18V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M16 14C16.5523 14 17 13.5523 17 13C17 12.4477 16.5523 12 16 12C15.4477 12 15 12.4477 15 13C15 13.5523 15.4477 14 16 14Z" fill="currentColor"/>
                      <path d="M19 14C19.5523 14 20 13.5523 20 13C20 12.4477 19.5523 12 19 12C18.4477 12 18 12.4477 18 13C18 13.5523 18.4477 14 19 14Z" fill="currentColor"/>
                      <path d="M2 10.5H22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="font-medium">Credit Card</span>
                  </button>

                  <button
                    @click="paymentMethod = 'mpesa'"
                    class="flex-1 py-3 px-4 rounded-lg border-2 transition-colors flex items-center justify-center space-x-2"
                    :class="[
                      paymentMethod === 'mpesa'
                        ? 'border-primary dark:border-primary-500 bg-primary/5 dark:bg-primary-900/10'
                        : 'border-gray-200 dark:border-gray-700'
                    ]"
                  >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z" fill="currentColor"/>
                      <path d="M12 17C14.7614 17 17 14.7614 17 12C17 9.23858 14.7614 7 12 7C9.23858 7 7 9.23858 7 12C7 14.7614 9.23858 17 12 17Z" fill="currentColor"/>
                    </svg>
                    <span class="font-medium">M-Pesa</span>
                  </button>
                </div>

                <!-- Stripe Payment Form -->
                <div v-if="paymentMethod === 'stripe'" class="space-y-4">
                  <div class="form-group">
                    <label for="card-number" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Card Number</label>
                    <input
                      type="text"
                      id="card-number"
                      v-model="stripeForm.cardNumber"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-text-light dark:text-text-dark"
                      placeholder="1234 5678 9012 3456"
                    />
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                      <label for="expiry" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Expiry Date</label>
                      <input
                        type="text"
                        id="expiry"
                        v-model="stripeForm.expiry"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-text-light dark:text-text-dark"
                        placeholder="MM/YY"
                      />
                    </div>

                    <div class="form-group">
                      <label for="cvc" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">CVC</label>
                      <input
                        type="text"
                        id="cvc"
                        v-model="stripeForm.cvc"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-text-light dark:text-text-dark"
                        placeholder="123"
                      />
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="name" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Name on Card</label>
                    <input
                      type="text"
                      id="name"
                      v-model="stripeForm.name"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-text-light dark:text-text-dark"
                      placeholder="John Doe"
                    />
                  </div>
                </div>

                <!-- M-Pesa Payment Form -->
                <div v-if="paymentMethod === 'mpesa'" class="space-y-4">
                  <div class="form-group">
                    <label for="phone-number" class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Phone Number</label>
                    <input
                      type="text"
                      id="phone-number"
                      v-model="mpesaForm.phoneNumber"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-text-light dark:text-text-dark"
                      placeholder="254XXXXXXXXX"
                    />
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter your M-Pesa registered phone number starting with 254</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-8 flex justify-between">
              <button
                @click="prevStep"
                class="py-2 px-6 border border-gray-300 dark:border-gray-600 rounded-lg text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              >
                Back
              </button>

              <button
                @click="processPayment"
                class="py-2 px-6 bg-primary dark:bg-primary-600 text-white rounded-lg hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors flex items-center"
                :disabled="isProcessingPayment"
                :class="{'opacity-50 cursor-not-allowed': isProcessingPayment}"
              >
                <span v-if="isProcessingPayment" class="mr-2">
                  <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                <span>{{ isProcessingPayment ? 'Processing...' : 'Complete Payment' }}</span>
              </button>
            </div>
          </div>

          <!-- Confirmation Step -->
          <div v-if="currentStep === 2" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="text-center py-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-green-500 dark:text-green-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>

              <h2 class="text-2xl font-semibold text-text-light dark:text-text-dark mb-2">Payment Successful!</h2>
              <p class="text-gray-600 dark:text-gray-400 mb-6">Your booking has been confirmed and tickets have been issued.</p>

              <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <router-link
                  :to="{ name: 'my-tickets' }"
                  class="py-2 px-6 bg-primary dark:bg-primary-600 text-white rounded-lg hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors"
                >
                  View My Tickets
                </router-link>

                <router-link
                  :to="{ name: 'events' }"
                  class="py-2 px-6 border border-gray-300 dark:border-gray-600 rounded-lg text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  Browse More Events
                </router-link>
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

            <div class="space-y-3 mb-6">
              <div class="flex justify-between">
                <div>
                  <span class="text-text-light dark:text-text-dark">{{ booking.ticket_type?.name }}</span>
                  <span class="text-gray-500 dark:text-gray-400 ml-1">× {{ booking.quantity }}</span>
                </div>
                <span class="text-text-light dark:text-text-dark">${{ ((booking.ticket_type?.price ?? 0) * booking.quantity).toFixed(2) }}</span>
              </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-6">
              <div class="flex justify-between font-medium">
                <span class="text-text-light dark:text-text-dark">Total</span>
                <span class="text-text-light dark:text-text-dark">${{ booking.total_price.toFixed(2) }}</span>
              </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
              <h3 class="font-medium text-text-light dark:text-text-dark mb-2">Booking Details</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium">Reference:</span> {{ booking.booking_reference }}
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium">Status:</span> {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium">Date:</span> {{ formatDate(booking.created_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/notification';
import BookingService from '@/services/api/BookingService';
import PaymentService from '@/services/api/PaymentService';
import type { Booking } from '@/services/api/BookingService';

// Services and stores
const route = useRoute();
const router = useRouter();
const notificationStore = useNotificationStore();
const bookingService = BookingService.getInstance();
const paymentService = PaymentService.getInstance();

// State
const loading = ref(true);
const error = ref('');
const booking = ref<Booking | null>(null);
const currentStep = ref(0);
const paymentMethod = ref<'stripe' | 'mpesa'>('stripe');
const isProcessingPayment = ref(false);
const paymentStatus = ref<'idle' | 'processing' | 'success' | 'error'>('idle');

// Form state
const stripeForm = ref({
  cardNumber: '',
  expiry: '',
  cvc: '',
  name: ''
});

const mpesaForm = ref({
  phoneNumber: ''
});

// Steps
const steps = [
  { id: 'review', name: 'Review' },
  { id: 'payment', name: 'Payment' },
  { id: 'confirmation', name: 'Confirmation' }
];

// Methods
const fetchBooking = async () => {
  try {
    const bookingId = route.query.booking_id;

    if (!bookingId) {
      error.value = 'No booking ID provided';
      return;
    }

    booking.value = await bookingService.getBooking(Number(bookingId));
  } catch (err: any) {
    console.error('Error fetching booking:', err);
    error.value = err.message || 'Failed to load booking details';
  } finally {
    loading.value = false;
  }
};

const nextStep = () => {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++;
  }
};

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
};

const goBack = () => {
  router.go(-1);
};

const processPayment = async () => {
  if (!booking.value || isProcessingPayment.value) return;

  isProcessingPayment.value = true;
  paymentStatus.value = 'processing';

  try {
    if (paymentMethod.value === 'stripe') {
      // In a real implementation, you would use Stripe.js to create a payment method
      // and then pass the payment method ID to your backend

      // Simulate a successful payment
      await new Promise(resolve => setTimeout(resolve, 2000));

      // Mock payment processing
      await paymentService.processStripePayment({
        booking_id: booking.value.id,
        payment_method: 'stripe',
        payment_token: 'pm_card_visa', // This would be a real token from Stripe.js
        currency: 'usd'
      });

      // Set success status and show animation
      paymentStatus.value = 'success';
      notificationStore.success('Payment processed successfully');

      // Wait for animation to complete before moving to next step
      setTimeout(() => {
        nextStep();
      }, 1500);
    } else if (paymentMethod.value === 'mpesa') {
      // Validate phone number
      if (!mpesaForm.value.phoneNumber) {
        notificationStore.error('Please enter your phone number');
        isProcessingPayment.value = false;
        paymentStatus.value = 'idle';
        return;
      }

      // Initiate M-Pesa payment
      await paymentService.initiateMPesaPayment({
        booking_id: booking.value.id,
        phone_number: mpesaForm.value.phoneNumber
      });

      notificationStore.success('M-Pesa payment request sent. Please check your phone to complete the payment.');

      // In a real implementation, you would poll the server to check the payment status
      // For now, we'll simulate a successful payment after a delay
      await new Promise(resolve => setTimeout(resolve, 3000));

      // Set success status and show animation
      paymentStatus.value = 'success';

      // Wait for animation to complete before moving to next step
      setTimeout(() => {
        nextStep();
      }, 1500);
    }
  } catch (err: any) {
    console.error('Payment error:', err);
    paymentStatus.value = 'error';
    notificationStore.error(err.message || 'Payment processing failed');

    // Reset payment status after showing error animation
    setTimeout(() => {
      paymentStatus.value = 'idle';
    }, 3000);
  } finally {
    isProcessingPayment.value = false;
  }
};

const formatDateTime = (dateString: string | undefined | null) => {
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

const formatDate = (dateString: string) => {
  if (!dateString) return '';

  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Lifecycle hooks
onMounted(async () => {
  await fetchBooking();
});
</script>

<style scoped>
/* Success Checkmark Animation */
.success-checkmark {
  width: 80px;
  height: 80px;
  margin: 0 auto;
}

.success-checkmark .check-icon {
  width: 80px;
  height: 80px;
  position: relative;
  border-radius: 50%;
  box-sizing: content-box;
  border: 4px solid #4CAF50;
}

.success-checkmark .check-icon::before {
  top: 3px;
  left: -2px;
  width: 30px;
  transform-origin: 100% 50%;
  border-radius: 100px 0 0 100px;
}

.success-checkmark .check-icon::after {
  top: 0;
  left: 30px;
  width: 60px;
  transform-origin: 0 50%;
  border-radius: 0 100px 100px 0;
  animation: rotate-circle 4.25s ease-in;
}

.success-checkmark .check-icon::before, .success-checkmark .check-icon::after {
  content: '';
  height: 100px;
  position: absolute;
  background: #FFFFFF;
  transform: rotate(-45deg);
}

.success-checkmark .check-icon .icon-line {
  height: 5px;
  background-color: #4CAF50;
  display: block;
  border-radius: 2px;
  position: absolute;
  z-index: 10;
}

.success-checkmark .check-icon .icon-line.line-tip {
  top: 46px;
  left: 14px;
  width: 25px;
  transform: rotate(45deg);
  animation: icon-line-tip 0.75s;
}

.success-checkmark .check-icon .icon-line.line-long {
  top: 38px;
  right: 8px;
  width: 47px;
  transform: rotate(-45deg);
  animation: icon-line-long 0.75s;
}

.success-checkmark .check-icon .icon-circle {
  top: -4px;
  left: -4px;
  z-index: 10;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  position: absolute;
  box-sizing: content-box;
  border: 4px solid rgba(76, 175, 80, 0.5);
}

.success-checkmark .check-icon .icon-fix {
  top: 8px;
  width: 5px;
  left: 26px;
  z-index: 1;
  height: 85px;
  position: absolute;
  transform: rotate(-45deg);
  background-color: #FFFFFF;
}

@keyframes rotate-circle {
  0% {
    transform: rotate(-45deg);
  }
  5% {
    transform: rotate(-45deg);
  }
  12% {
    transform: rotate(-405deg);
  }
  100% {
    transform: rotate(-405deg);
  }
}

@keyframes icon-line-tip {
  0% {
    width: 0;
    left: 1px;
    top: 19px;
  }
  54% {
    width: 0;
    left: 1px;
    top: 19px;
  }
  70% {
    width: 50px;
    left: -8px;
    top: 37px;
  }
  84% {
    width: 17px;
    left: 21px;
    top: 48px;
  }
  100% {
    width: 25px;
    left: 14px;
    top: 45px;
  }
}

@keyframes icon-line-long {
  0% {
    width: 0;
    right: 46px;
    top: 54px;
  }
  65% {
    width: 0;
    right: 46px;
    top: 54px;
  }
  84% {
    width: 55px;
    right: 0px;
    top: 35px;
  }
  100% {
    width: 47px;
    right: 8px;
    top: 38px;
  }
}

/* Error X Mark Animation */
.error-x-mark {
  width: 80px;
  height: 80px;
  margin: 0 auto;
}

.error-x-mark .x-icon {
  position: relative;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid #F44336;
  box-sizing: content-box;
  padding: 0;
}

.error-x-mark .x-icon .icon-line {
  position: absolute;
  height: 5px;
  width: 47px;
  background-color: #F44336;
  border-radius: 2px;
  top: 37px;
  left: 17px;
}

.error-x-mark .x-icon .icon-line.line-left {
  transform: rotate(45deg);
  animation: error-icon-line-left 0.75s;
}

.error-x-mark .x-icon .icon-line.line-right {
  transform: rotate(-45deg);
  animation: error-icon-line-right 0.75s;
}

@keyframes error-icon-line-left {
  0% {
    transform: rotate(45deg);
    width: 0;
    left: 27px;
  }
  54% {
    transform: rotate(45deg);
    width: 0;
    left: 27px;
  }
  70% {
    transform: rotate(45deg);
    width: 50px;
    left: 14px;
  }
  84% {
    transform: rotate(45deg);
    width: 50px;
    left: 17px;
  }
  100% {
    transform: rotate(45deg);
    width: 47px;
    left: 17px;
  }
}

@keyframes error-icon-line-right {
  0% {
    transform: rotate(-45deg);
    width: 0;
    right: 27px;
  }
  54% {
    transform: rotate(-45deg);
    width: 0;
    right: 27px;
  }
  70% {
    transform: rotate(-45deg);
    width: 50px;
    right: 14px;
  }
  84% {
    transform: rotate(-45deg);
    width: 50px;
    right: 17px;
  }
  100% {
    transform: rotate(-45deg);
    width: 47px;
    right: 17px;
  }
}
</style>
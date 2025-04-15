<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center">
          <router-link
            :to="{ name: 'my-tickets' }"
            class="mr-4 text-gray-500 dark:text-gray-400 hover:text-text-light dark:hover:text-text-dark transition-colors"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </router-link>

          <h1 class="text-2xl sm:text-3xl font-bold text-text-light dark:text-text-dark">
            Ticket Details
          </h1>
        </div>
      </div>

      <div v-if="loading" class="animate-pulse space-y-6">
        <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
        <div class="h-40 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
      </div>

      <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-red-500 dark:text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h2 class="text-xl font-semibold text-red-800 dark:text-red-300 mb-2">{{ error }}</h2>
        <p class="text-red-600 dark:text-red-400 mb-4">Please try again or contact support if the problem persists.</p>
        <router-link
          :to="{ name: 'my-tickets' }"
          class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors inline-block"
        >
          Back to My Tickets
        </router-link>
      </div>

      <div v-else-if="booking">
        <!-- Digital Ticket -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-8">
          <div class="relative">
            <!-- Status Badge -->
            <div
              class="absolute top-4 right-4 px-3 py-1 rounded-full text-sm font-medium z-10"
              :class="getStatusClass(booking.status)"
            >
              {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
            </div>

            <!-- Event Banner -->
            <div class="h-48 bg-gray-200 dark:bg-gray-700 relative">
              <img
                v-if="booking.event?.image_url"
                :src="booking.event.image_url"
                :alt="booking.event.title"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>

              <!-- Overlay Gradient -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>

              <!-- Event Title -->
              <div class="absolute bottom-0 left-0 right-0 p-6">
                <h2 class="text-xl sm:text-2xl font-bold text-white">
                  {{ booking.event?.title || 'Unknown Event' }}
                </h2>
                <p class="text-white/80 text-sm">
                  {{ formatDateTime(booking.event?.start_time) }}
                </p>
              </div>
            </div>

            <!-- Ticket Content -->
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Ticket Info -->
                <div>
                  <h3 class="font-semibold text-text-light dark:text-text-dark mb-4">Ticket Information</h3>

                  <div class="space-y-3">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Ticket Type</p>
                      <p class="font-medium text-text-light dark:text-text-dark">{{ booking.ticket_type?.name || 'Standard Ticket' }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Quantity</p>
                      <p class="font-medium text-text-light dark:text-text-dark">{{ booking.quantity }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Booking Reference</p>
                      <p class="font-medium text-text-light dark:text-text-dark font-mono">{{ booking.booking_reference }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Purchase Date</p>
                      <p class="font-medium text-text-light dark:text-text-dark">{{ formatDate(booking.created_at) }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Price</p>
                      <p class="font-medium text-text-light dark:text-text-dark">${{ booking.total_price.toFixed(2) }}</p>
                    </div>
                  </div>
                </div>

                <!-- QR Code -->
                <div class="flex flex-col items-center justify-center">
                  <div class="bg-white p-4 rounded-lg shadow-md mb-3">
                    <img
                      v-if="qrCodeUrl"
                      :src="qrCodeUrl"
                      alt="Ticket QR Code"
                      class="w-48 h-48"
                    />
                    <div v-else class="w-48 h-48 flex items-center justify-center bg-gray-100">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                      </svg>
                    </div>
                  </div>
                  <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                    Present this QR code at the event entrance
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Event Details -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
          <h3 class="font-semibold text-text-light dark:text-text-dark mb-4">Event Details</h3>

          <div class="space-y-4">
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Location</p>
              <p class="font-medium text-text-light dark:text-text-dark">{{ booking.event?.location || 'Location not specified' }}</p>
            </div>

            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Date & Time</p>
              <p class="font-medium text-text-light dark:text-text-dark">{{ formatDateTime(booking.event?.start_time) }}</p>
            </div>

            <div v-if="booking.event?.description">
              <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
              <p class="text-text-light dark:text-text-dark">{{ booking.event.description }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-4 justify-center">
          <button
            @click="downloadTicket"
            class="py-2 px-6 bg-primary dark:bg-primary-600 text-white rounded-lg hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors flex items-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download Ticket
          </button>

          <button
            @click="downloadReceipt"
            class="py-2 px-6 border border-gray-300 dark:border-gray-600 rounded-lg text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download Receipt
          </button>

          <button
            @click="printReceipt"
            class="py-2 px-6 border border-gray-300 dark:border-gray-600 rounded-lg text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Receipt
          </button>

          <button
            v-if="canTransfer(booking)"
            @click="openTransferModal"
            class="py-2 px-6 border border-gray-300 dark:border-gray-600 rounded-lg text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
            Transfer Ticket
          </button>

          <button
            v-if="canCancel(booking)"
            @click="openCancelModal"
            class="py-2 px-6 border border-red-300 dark:border-red-800 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors flex items-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Cancel Booking
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
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/notification';
import BookingService from '@/services/api/BookingService';
import PaymentService from '@/services/api/PaymentService';
import TicketService from '@/services/api/TicketService';
import type { Booking } from '@/services/api/BookingService';

// Services and stores
const route = useRoute();
const router = useRouter();
const notificationStore = useNotificationStore();
const bookingService = BookingService.getInstance();
const paymentService = PaymentService.getInstance();
const ticketService = TicketService.getInstance();

// State
const loading = ref(true);
const error = ref('');
const booking = ref<Booking | null>(null);
const qrCodeUrl = ref<string | null>(null);

// Modal state
const showTransferModal = ref(false);
const showCancelModal = ref(false);
const transferEmail = ref('');
const transferError = ref('');
const isProcessing = ref(false);

// Methods
const fetchBooking = async () => {
  try {
    const bookingId = Number(route.params.id);
    booking.value = await bookingService.getBooking(bookingId);

    // Fetch QR code
    if (booking.value.status === 'confirmed') {
      try {
        qrCodeUrl.value = await bookingService.getQRCode(bookingId);
      } catch (err) {
        console.error('Error fetching QR code:', err);
        // Don't show error to user, just log it
      }
    }

    // Save ticket for offline access
    if (booking.value && qrCodeUrl.value) {
      ticketService.saveTicketOffline({
        id: 0, // This would be a real ticket ID in a real implementation
        booking_id: booking.value.id,
        ticket_number: booking.value.booking_reference,
        qr_code_url: qrCodeUrl.value,
        is_used: false,
        created_at: booking.value.created_at,
        updated_at: booking.value.updated_at,
        booking: booking.value
      });
    }
  } catch (err: any) {
    console.error('Error fetching booking:', err);
    error.value = err.message || 'Failed to load ticket details';
  } finally {
    loading.value = false;
  }
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

const formatDate = (dateString: string) => {
  if (!dateString) return '';

  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const downloadTicket = async () => {
  if (!booking.value) return;

  try {
    // In a real implementation, this would download a PDF ticket
    // For now, we'll just show a notification
    notificationStore.success('Ticket downloaded successfully');
  } catch (err: any) {
    console.error('Error downloading ticket:', err);
    notificationStore.error(err.message || 'Failed to download ticket');
  }
};

const downloadReceipt = async () => {
  if (!booking.value) return;

  try {
    isProcessing.value = true;

    // Get payment for this booking
    const payment = await paymentService.getPaymentByBooking(booking.value.id);

    // Generate and download receipt PDF
    const pdfUrl = await paymentService.generateReceipt(payment.id);

    // Create an anchor element and trigger download
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.download = `Receipt-${booking.value.booking_reference}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    notificationStore.success('Receipt downloaded successfully');
  } catch (err: any) {
    console.error('Error downloading receipt:', err);
    notificationStore.error(err.message || 'Failed to download receipt');
  } finally {
    isProcessing.value = false;
  }
};

// Print receipt
const printReceipt = async () => {
  if (!booking.value) return;

  try {
    isProcessing.value = true;

    // Create a printable version of the receipt
    const printWindow = window.open('', '_blank');

    if (!printWindow) {
      notificationStore.error('Please allow pop-ups to print receipts');
      return;
    }

    // Generate receipt HTML
    const receiptHtml = generateReceiptHtml(booking.value);

    // Write to the new window
    printWindow.document.write(receiptHtml);
    printWindow.document.close();

    // Wait for resources to load then print
    printWindow.onload = () => {
      printWindow.print();
      // Close the window after printing (optional)
      // printWindow.close();
    };

    notificationStore.success('Receipt prepared for printing');
  } catch (err: any) {
    console.error('Error printing receipt:', err);
    notificationStore.error('Failed to print receipt');
  } finally {
    isProcessing.value = false;
  }
};

const generateReceiptHtml = (booking: Booking) => {
  const event = booking.event;
  const ticketType = booking.ticket_type;

  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Receipt - ${event?.title || 'Event Ticket'}</title>
      <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .receipt { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 150px; margin-bottom: 10px; }
        .details { margin-bottom: 30px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details th { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        .details td { padding: 8px; border-bottom: 1px solid #ddd; }
        .total { font-weight: bold; font-size: 1.2em; margin-top: 20px; text-align: right; }
        .footer { margin-top: 50px; text-align: center; font-size: 0.9em; color: #666; }
        @media print {
          body { -webkit-print-color-adjust: exact; }
        }
      </style>
    </head>
    <body>
      <div class="receipt">
        <div class="header">
          <h1>VentlyX Receipt</h1>
          <p>Booking Reference: ${booking.booking_reference}</p>
          <p>Date: ${new Date(booking.created_at).toLocaleDateString()}</p>
        </div>

        <div class="details">
          <h2>${event?.title || 'Event Ticket'}</h2>
          <p>${formatDateTime(event?.start_time)}</p>
          <p>${event?.location || 'Location not specified'}</p>

          <table>
            <tr>
              <th>Ticket Type</th>
              <th>Quantity</th>
              <th>Price</th>
              <th>Subtotal</th>
            </tr>
            <tr>
              <td>${ticketType?.name || 'Standard Ticket'}</td>
              <td>${booking.quantity}</td>
              <td>$${(ticketType?.price || 0).toFixed(2)}</td>
              <td>$${booking.total_price.toFixed(2)}</td>
            </tr>
          </table>

          <div class="total">
            Total: $${booking.total_price.toFixed(2)}
          </div>
        </div>

        <div class="footer">
          <p>Thank you for your purchase!</p>
          <p>For any questions, please contact support@ventlyx.com</p>
        </div>
      </div>
    </body>
    </html>
  `;
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

const openTransferModal = () => {
  transferEmail.value = '';
  transferError.value = '';
  showTransferModal.value = true;
};

const closeTransferModal = () => {
  showTransferModal.value = false;
};

const transferTicket = async () => {
  if (!booking.value || isProcessing.value) return;

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
    await bookingService.transferBooking(booking.value.id, transferEmail.value);

    notificationStore.success('Ticket transfer initiated successfully');
    closeTransferModal();

    // Refresh booking data
    await fetchBooking();
  } catch (err: any) {
    console.error('Error transferring ticket:', err);
    transferError.value = err.message || 'Failed to transfer ticket';
  } finally {
    isProcessing.value = false;
  }
};

const openCancelModal = () => {
  showCancelModal.value = true;
};

const closeCancelModal = () => {
  showCancelModal.value = false;
};

const cancelBooking = async () => {
  if (!booking.value || isProcessing.value) return;

  isProcessing.value = true;

  try {
    await bookingService.cancelBooking(booking.value.id);

    notificationStore.success('Booking cancelled successfully');
    closeCancelModal();

    // Refresh booking data
    await fetchBooking();
  } catch (err: any) {
    console.error('Error cancelling booking:', err);
    notificationStore.error(err.message || 'Failed to cancel booking');
  } finally {
    isProcessing.value = false;
  }
};

// Lifecycle hooks
onMounted(async () => {
  await fetchBooking();
});
</script>

<template>
  <div class="bg-background-light dark:bg-background-dark min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Dashboard Header -->
      <div class="mb-8">
        <h1
          class="text-2xl sm:text-3xl font-bold text-text-light dark:text-text-dark"
        >
          Dashboard
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Welcome back, {{ user?.name || "User" }}!
        </p>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div
          class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary dark:border-primary-400"
        ></div>
      </div>

      <div
        v-else-if="error"
        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-12 w-12 mx-auto text-red-500 dark:text-red-400 mb-4"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <h2 class="text-xl font-semibold text-red-800 dark:text-red-300 mb-2">
          {{ error }}
        </h2>
        <p class="text-red-600 dark:text-red-400 mb-4">
          Please try again or contact support if the problem persists.
        </p>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div
                  class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mr-4"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Bookings
                  </p>
                  <p
                    class="text-xl font-semibold text-text-light dark:text-text-dark"
                  >
                    {{ dashboardData?.stats?.total_bookings || 0 }}
                  </p>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div
                  class="p-3 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 mr-4"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Upcoming Events
                  </p>
                  <p
                    class="text-xl font-semibold text-text-light dark:text-text-dark"
                  >
                    {{ dashboardData?.stats?.upcoming_events || 0 }}
                  </p>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div
                  class="p-3 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 mr-4"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Spent
                  </p>
                  <p
                    class="text-xl font-semibold text-text-light dark:text-text-dark"
                  >
                    ${{
                      dashboardData?.stats?.total_spent?.toFixed(2) || "0.00"
                    }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Upcoming Events -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden"
          >
            <div
              class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center"
            >
              <h2
                class="text-lg font-semibold text-text-light dark:text-text-dark"
              >
                Upcoming Events
              </h2>
              <router-link
                to="/tickets"
                class="text-sm text-primary dark:text-primary-400 hover:underline"
                >View All</router-link
              >
            </div>

            <div
              v-if="dashboardData?.upcoming_events?.length"
              class="divide-y divide-gray-200 dark:divide-gray-700"
            >
              <div
                v-for="event in dashboardData.upcoming_events"
                :key="event.id"
                class="p-6 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
              >
                <div class="flex items-start">
                  <div class="flex-shrink-0 mr-4">
                    <div
                      class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-lg flex flex-col items-center justify-center text-center"
                    >
                      <span
                        class="text-sm font-medium text-gray-600 dark:text-gray-300"
                        >{{ formatEventDate(event.start_time).month }}</span
                      >
                      <span
                        class="text-lg font-bold text-text-light dark:text-text-dark"
                        >{{ formatEventDate(event.start_time).day }}</span
                      >
                    </div>
                  </div>
                  <div class="flex-grow">
                    <h3
                      class="text-base font-medium text-text-light dark:text-text-dark"
                    >
                      {{ event.title }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                      {{ formatEventTime(event.start_time) }} •
                      {{ event.location }}
                    </p>
                  </div>
                  <div class="flex-shrink-0 ml-4">
                    <router-link
                      :to="`/tickets/${event.booking_id}`"
                      class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-primary dark:bg-primary-600 hover:bg-primary-dark dark:hover:bg-primary-700 transition-colors"
                    >
                      View Ticket
                    </router-link>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="p-6 text-center">
              <p class="text-gray-500 dark:text-gray-400">
                You don't have any upcoming events.
              </p>
              <router-link
                to="/events"
                class="mt-2 inline-block text-primary dark:text-primary-400 hover:underline"
                >Browse Events</router-link
              >
            </div>
          </div>

          <!-- Recent Bookings -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden"
          >
            <div
              class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
            >
              <h2
                class="text-lg font-semibold text-text-light dark:text-text-dark"
              >
                Recent Bookings
              </h2>
            </div>

            <div
              v-if="dashboardData?.recent_bookings?.length"
              class="overflow-x-auto"
            >
              <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
              >
                <thead class="bg-gray-50 dark:bg-gray-750">
                  <tr>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Event
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Date
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Status
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Amount
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody
                  class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                >
                  <tr
                    v-for="booking in dashboardData.recent_bookings"
                    :key="booking.id"
                    class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
                  >
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div
                        class="text-sm font-medium text-text-light dark:text-text-dark"
                      >
                        {{ booking.event_title }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ formatDate(booking.booking_date) }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="getStatusClass(booking.status)"
                      >
                        {{
                          booking.status.charAt(0).toUpperCase() +
                          booking.status.slice(1)
                        }}
                      </span>
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                    >
                      ${{ booking.total_price.toFixed(2) }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                    >
                      <router-link
                        :to="`/tickets/${booking.id}`"
                        class="text-primary dark:text-primary-400 hover:text-primary-dark dark:hover:text-primary-300"
                        >View</router-link
                      >
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else class="p-6 text-center">
              <p class="text-gray-500 dark:text-gray-400">
                You don't have any recent bookings.
              </p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
          <!-- Calendar Widget -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden"
          >
            <div
              class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
            >
              <h2
                class="text-lg font-semibold text-text-light dark:text-text-dark"
              >
                Calendar
              </h2>
            </div>
            <div class="p-4">
              <!-- Calendar component would go here -->
              <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                Calendar view coming soon
              </p>
            </div>
          </div>

          <!-- Saved Events -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden"
          >
            <div
              class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center"
            >
              <h2
                class="text-lg font-semibold text-text-light dark:text-text-dark"
              >
                Saved Events
              </h2>
            </div>

            <div
              v-if="dashboardData?.saved_events?.length"
              class="divide-y divide-gray-200 dark:divide-gray-700"
            >
              <div
                v-for="event in dashboardData.saved_events"
                :key="event.id"
                class="p-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
              >
                <h3
                  class="text-sm font-medium text-text-light dark:text-text-dark"
                >
                  {{ event.title }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ formatDate(event.start_time) }}
                </p>
                <div class="mt-2 flex justify-between items-center">
                  <router-link
                    :to="`/events/${event.id}`"
                    class="text-xs text-primary dark:text-primary-400 hover:underline"
                    >View Details</router-link
                  >
                  <button
                    @click="toggleSavedEvent(event.id)"
                    class="text-xs text-red-600 dark:text-red-400 hover:underline"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="p-6 text-center">
              <p class="text-gray-500 dark:text-gray-400">
                You don't have any saved events.
              </p>
              <router-link
                to="/events"
                class="mt-2 inline-block text-primary dark:text-primary-400 hover:underline"
                >Browse Events</router-link
              >
            </div>
          </div>

          <!-- Notifications -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden"
          >
            <div
              class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center"
            >
              <h2
                class="text-lg font-semibold text-text-light dark:text-text-dark"
              >
                Notifications
              </h2>
              <button
                v-if="hasUnreadNotifications"
                @click="markAllNotificationsAsRead"
                class="text-xs text-primary dark:text-primary-400 hover:underline"
              >
                Mark all as read
              </button>
            </div>

            <div
              v-if="notifications.length"
              class="divide-y divide-gray-200 dark:divide-gray-700 max-h-80 overflow-y-auto"
            >
              <div
                v-for="notification in notifications"
                :key="notification.id"
                class="p-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
                :class="{
                  'bg-blue-50 dark:bg-blue-900/10': !notification.read_at,
                }"
              >
                <div class="flex">
                  <div class="flex-shrink-0 mr-3">
                    <div
                      class="h-8 w-8 rounded-full bg-primary dark:bg-primary-600 flex items-center justify-center"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                      </svg>
                    </div>
                  </div>
                  <div class="flex-grow">
                    <p class="text-sm text-text-light dark:text-text-dark">
                      {{ notification.message }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      {{ formatNotificationTime(notification.created_at) }}
                    </p>
                  </div>
                  <div class="flex-shrink-0 ml-3">
                    <button
                      v-if="!notification.read_at"
                      @click="markNotificationAsRead(notification.id)"
                      class="text-xs text-primary dark:text-primary-400 hover:underline"
                    >
                      Mark as read
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="p-6 text-center">
              <p class="text-gray-500 dark:text-gray-400">
                You don't have any notifications.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useNotificationStore } from "@/stores/notification";
import dashboardService from "@/services/api/dashboard";
import type {
  EventDate,
  Event,
  Booking,
  Notification,
  DashboardData,
} from "@/services/api/dashboard";

const authStore = useAuthStore();
const notificationStore = useNotificationStore();
const user = computed(() => authStore.user);

const loading = ref(true);
const error = ref("");
const dashboardData = ref<DashboardData | null>(null);
const notifications = ref<Notification[]>([]);

const hasUnreadNotifications = computed(() => {
  return notifications.value.some((notification) => !notification.read_at);
});

const fetchDashboardData = async () => {
  loading.value = true;
  error.value = "";

  try {
    // Fetch dashboard data from the API
    dashboardData.value = await dashboardService.getUserDashboard();

    // Fetch notifications
    notifications.value = await dashboardService.getNotifications();
  } catch (err: any) {
    console.error("Error fetching dashboard data:", err);
    error.value = err.message || "Failed to load dashboard data";
  } finally {
    loading.value = false;
  }
};

const formatEventDate = (dateString: string): EventDate => {
  const date = new Date(dateString);
  const month = date.toLocaleString("en-US", { month: "short" });
  const day = date.getDate().toString();

  return { month, day };
};

const formatEventTime = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleString("en-US", {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const formatNotificationTime = (dateString: string): string => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInDays = Math.floor(
    (now.getTime() - date.getTime()) / (1000 * 60 * 60 * 24)
  );

  if (diffInDays === 0) {
    return (
      "Today " +
      date.toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
      })
    );
  } else if (diffInDays === 1) {
    return (
      "Yesterday " +
      date.toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
      })
    );
  } else {
    return (
      date.toLocaleDateString("en-US", { month: "short", day: "numeric" }) +
      " " +
      date.toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
      })
    );
  }
};

const getStatusClass = (status: string): string => {
  switch (status) {
    case "confirmed":
      return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400";
    case "pending":
      return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400";
    case "completed":
      return "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400";
    case "cancelled":
      return "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400";
    default:
      return "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400";
  }
};

const toggleSavedEvent = async (eventId: number) => {
  try {
    await dashboardService.removeSavedEvent(eventId);

    if (dashboardData.value) {
      dashboardData.value.saved_events =
        dashboardData.value.saved_events.filter(
          (event) => event.id !== eventId
        );
    }

    notificationStore.success("Event removed from saved events");
  } catch (err: any) {
    notificationStore.error(err.message || "Failed to remove event");
  }
};

const markNotificationAsRead = async (notificationId: number) => {
  try {
    await dashboardService.markNotificationAsRead(notificationId);

    const notification = notifications.value.find(
      (n) => n.id === notificationId
    );
    if (notification) {
      notification.read_at = new Date().toISOString();
    }

    notificationStore.success("Notification marked as read");
  } catch (err: any) {
    notificationStore.error(
      err.message || "Failed to mark notification as read"
    );
  }
};

const markAllNotificationsAsRead = async () => {
  try {
    await dashboardService.markAllNotificationsAsRead();

    notifications.value.forEach((notification) => {
      if (!notification.read_at) {
        notification.read_at = new Date().toISOString();
      }
    });

    notificationStore.success("All notifications marked as read");
  } catch (err: any) {
    notificationStore.error(
      err.message || "Failed to mark all notifications as read"
    );
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

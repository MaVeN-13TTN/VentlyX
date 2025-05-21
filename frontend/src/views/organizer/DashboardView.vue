<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-2">Organizer Dashboard</h1>
    <p class="text-gray-600 mb-6">Welcome to your organizer dashboard.</p>

    <!-- Quick Actions -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <router-link
          :to="{ name: 'event-create' }"
          class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex flex-col items-center text-center"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-8 w-8 text-primary-600 mb-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 6v6m0 0v6m0-6h6m-6 0H6"
            />
          </svg>
          <h3 class="font-medium">Create Event</h3>
          <p class="text-sm text-gray-500 mt-1">Create a new event</p>
        </router-link>

        <router-link
          :to="{ name: 'organizer-events' }"
          class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex flex-col items-center text-center"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-8 w-8 text-primary-600 mb-2"
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
          <h3 class="font-medium">Manage Events</h3>
          <p class="text-sm text-gray-500 mt-1">View and edit your events</p>
        </router-link>

        <router-link
          :to="{ name: 'organizer-attendees' }"
          class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex flex-col items-center text-center"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-8 w-8 text-primary-600 mb-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            />
          </svg>
          <h3 class="font-medium">Manage Attendees</h3>
          <p class="text-sm text-gray-500 mt-1">View and check-in attendees</p>
        </router-link>

        <router-link
          :to="{ name: 'organizer-analytics' }"
          class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow flex flex-col items-center text-center"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-8 w-8 text-primary-600 mb-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
            />
          </svg>
          <h3 class="font-medium">Analytics</h3>
          <p class="text-sm text-gray-500 mt-1">
            View sales and attendance data
          </p>
        </router-link>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold mb-4">Overview</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-1">Total Events</h3>
          <p class="text-2xl font-bold">{{ stats.totalEvents }}</p>
          <p class="text-xs text-gray-500 mt-1">
            {{ stats.upcomingEvents }} upcoming
          </p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-1">Total Sales</h3>
          <p class="text-2xl font-bold">
            ${{ formatNumber(stats.totalSales) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Lifetime</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-1">Tickets Sold</h3>
          <p class="text-2xl font-bold">{{ stats.ticketsSold }}</p>
          <p class="text-xs text-gray-500 mt-1">Across all events</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-1">
            Avg. Ticket Price
          </h3>
          <p class="text-2xl font-bold">
            ${{ formatNumber(stats.avgTicketPrice) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Based on all sales</p>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div>
      <h2 class="text-lg font-semibold mb-4">Recent Activity</h2>
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div
          v-if="recentActivity.length === 0"
          class="p-6 text-center text-gray-500"
        >
          No recent activity to display.
        </div>
        <ul v-else class="divide-y divide-gray-200">
          <li
            v-for="(activity, index) in recentActivity"
            :key="index"
            class="p-4 hover:bg-gray-50"
          >
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0">
                <span
                  class="inline-flex items-center justify-center h-8 w-8 rounded-full"
                  :class="activityTypeClasses[activity.type]"
                >
                  <svg
                    v-if="activity.type === 'booking'"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
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
                  <svg
                    v-else-if="activity.type === 'check-in'"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">
                  {{ activity.message }}
                </p>
                <p class="text-sm text-gray-500 truncate">
                  {{ activity.eventTitle }}
                </p>
              </div>
              <div class="flex-shrink-0 text-sm text-gray-500">
                {{ formatTimeAgo(activity.timestamp) }}
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import organizerService from "@/services/api/organizer";

// Mock data for dashboard
const stats = ref({
  totalEvents: 0,
  upcomingEvents: 0,
  totalSales: 0,
  ticketsSold: 0,
  avgTicketPrice: 0,
});

const recentActivity = ref<
  Array<{
    type: "booking" | "check-in" | "event";
    message: string;
    eventTitle: string;
    timestamp: string;
  }>
>([]);

const activityTypeClasses = {
  booking: "bg-blue-100 text-blue-600",
  "check-in": "bg-green-100 text-green-600",
  event: "bg-purple-100 text-purple-600",
};

// Format number with commas
const formatNumber = (value: number) => {
  return new Intl.NumberFormat("en-US", { maximumFractionDigits: 2 }).format(
    value
  );
};

// Format time ago
const formatTimeAgo = (timestamp: string) => {
  const now = new Date();
  const date = new Date(timestamp);
  const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);

  let interval = Math.floor(seconds / 31536000);
  if (interval >= 1) return `${interval} year${interval === 1 ? "" : "s"} ago`;

  interval = Math.floor(seconds / 2592000);
  if (interval >= 1) return `${interval} month${interval === 1 ? "" : "s"} ago`;

  interval = Math.floor(seconds / 86400);
  if (interval >= 1) return `${interval} day${interval === 1 ? "" : "s"} ago`;

  interval = Math.floor(seconds / 3600);
  if (interval >= 1) return `${interval} hour${interval === 1 ? "" : "s"} ago`;

  interval = Math.floor(seconds / 60);
  if (interval >= 1)
    return `${interval} minute${interval === 1 ? "" : "s"} ago`;

  return `${Math.floor(seconds)} second${seconds === 1 ? "" : "s"} ago`;
};

// Fetch dashboard data
const fetchDashboardData = async () => {
  try {
    // This would be replaced with an actual API call
    // For now, we'll use mock data
    await new Promise((resolve) => setTimeout(resolve, 500)); // Simulate API delay

    // Mock stats
    stats.value = {
      totalEvents: 12,
      upcomingEvents: 5,
      totalSales: 15750.5,
      ticketsSold: 325,
      avgTicketPrice: 48.46,
    };

    // Mock recent activity
    recentActivity.value = [
      {
        type: "booking",
        message: "New ticket purchased by John Doe",
        eventTitle: "Tech Conference 2023",
        timestamp: new Date(Date.now() - 25 * 60 * 1000).toISOString(), // 25 minutes ago
      },
      {
        type: "check-in",
        message: "Jane Smith checked in",
        eventTitle: "Networking Mixer",
        timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(), // 2 hours ago
      },
      {
        type: "event",
        message: "Event published",
        eventTitle: "Annual Charity Gala",
        timestamp: new Date(Date.now() - 5 * 60 * 60 * 1000).toISOString(), // 5 hours ago
      },
      {
        type: "booking",
        message: "New ticket purchased by Robert Johnson",
        eventTitle: "Tech Conference 2023",
        timestamp: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000).toISOString(), // 1 day ago
      },
      {
        type: "check-in",
        message: "Michael Brown checked in",
        eventTitle: "Workshop: Introduction to AI",
        timestamp: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString(), // 2 days ago
      },
    ];
  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  }
};

// Lifecycle hooks
onMounted(() => {
  fetchDashboardData();
});
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6"
    >
      <div>
        <h1 class="text-2xl font-bold mb-2">Analytics</h1>
        <p class="text-gray-600">View analytics for your events.</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <div class="flex space-x-4">
          <div>
            <label
              for="event-select"
              class="block text-sm font-medium text-gray-700 mb-1"
              >Event</label
            >
            <select
              id="event-select"
              v-model="selectedEventId"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
              @change="fetchEventAnalytics"
            >
              <option value="">All Events</option>
              <option v-for="event in events" :key="event.id" :value="event.id">
                {{ event.title }}
              </option>
            </select>
          </div>
          <div>
            <label
              for="date-range"
              class="block text-sm font-medium text-gray-700 mb-1"
              >Date Range</label
            >
            <select
              id="date-range"
              v-model="dateRange"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
              @change="fetchAnalytics"
            >
              <option value="7">Last 7 days</option>
              <option value="30">Last 30 days</option>
              <option value="90">Last 90 days</option>
              <option value="365">Last year</option>
              <option value="all">All time</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div
        class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"
      ></div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-6"
    >
      {{ error }}
    </div>

    <!-- Analytics Content -->
    <div v-else>
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-1">Total Sales</h3>
          <p class="text-2xl font-bold">
            ${{ formatNumber(analytics.sales_summary?.total_sales || 0) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">{{ dateRangeText }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-1">Tickets Sold</h3>
          <p class="text-2xl font-bold">
            {{ formatNumber(analytics.sales_summary?.total_tickets_sold || 0) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">{{ dateRangeText }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-1">
            Avg. Ticket Price
          </h3>
          <p class="text-2xl font-bold">
            ${{
              formatNumber(analytics.sales_summary?.average_ticket_price || 0)
            }}
          </p>
          <p class="text-xs text-gray-500 mt-1">{{ dateRangeText }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-1">Events</h3>
          <p class="text-2xl font-bold">
            {{ formatNumber(analytics.events_summary?.total_events || 0) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ analytics.events_summary?.upcoming_events || 0 }} upcoming
          </p>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Sales Over Time Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold mb-4">Sales Over Time</h2>
          <BaseChart
            v-if="salesChartData.labels.length > 0"
            type="line"
            :data="salesChartData"
            :options="salesChartOptions"
            height="300px"
          />
          <p v-else class="text-gray-500 text-center py-12">
            No sales data available for the selected period.
          </p>
        </div>

        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold mb-4">Revenue</h2>
          <BaseChart
            v-if="revenueChartData.labels.length > 0"
            type="bar"
            :data="revenueChartData"
            :options="revenueChartOptions"
            height="300px"
          />
          <p v-else class="text-gray-500 text-center py-12">
            No revenue data available for the selected period.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Ticket Type Distribution -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold mb-4">Ticket Type Distribution</h2>
          <BaseChart
            v-if="ticketTypeChartData.labels.length > 0"
            type="doughnut"
            :data="ticketTypeChartData"
            :options="ticketTypeChartOptions"
            height="300px"
          />
          <p v-else class="text-gray-500 text-center py-12">
            No ticket data available for the selected period.
          </p>
        </div>

        <!-- Popular Events -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold mb-4">Popular Events</h2>
          <div
            v-if="
              analytics.popular_events && analytics.popular_events.length > 0
            "
          >
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Event
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Tickets Sold
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Revenue
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="event in analytics.popular_events" :key="event.id">
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                    >
                      {{ event.title }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{ formatNumber(event.tickets_sold) }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      ${{ formatNumber(event.revenue) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <p v-else class="text-gray-500 text-center py-12">
            No event data available for the selected period.
          </p>
        </div>
      </div>

      <!-- Export Section -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Export Data</h2>
        <div class="flex flex-wrap gap-4">
          <button
            @click="exportSalesData"
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 flex items-center"
            :disabled="isExporting"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 mr-2"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            <span v-if="isExporting">Exporting...</span>
            <span v-else>Export Sales Data</span>
          </button>

          <button
            @click="exportAttendeeData"
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 flex items-center"
            :disabled="isExporting || !selectedEventId"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 mr-2"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            <span v-if="isExporting">Exporting...</span>
            <span v-else>Export Attendee List</span>
          </button>
        </div>
        <p
          v-if="!selectedEventId && !isExporting"
          class="text-sm text-gray-500 mt-2"
        >
          Select an event to export attendee data.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from "vue";
import { useNotificationStore } from "@/stores/notification";
import organizerService, { OrganizerEvent } from "@/services/api/organizer";
import BaseChart from "@/components/charts/BaseChart.vue";

// Initialize notification store
const notificationStore = useNotificationStore();

// State
const loading = ref(true);
const error = ref("");
const events = ref<OrganizerEvent[]>([]);
const selectedEventId = ref("");
const dateRange = ref("30"); // Default to last 30 days
const isExporting = ref(false);

// Analytics data
const analytics = reactive({
  events_summary: {
    total_events: 0,
    published_events: 0,
    draft_events: 0,
    upcoming_events: 0,
  },
  sales_summary: {
    total_sales: 0,
    total_tickets_sold: 0,
    average_ticket_price: 0,
  },
  popular_events: [] as Array<{
    id: number;
    title: string;
    tickets_sold: number;
    revenue: number;
  }>,
  sales_by_day: [] as Array<{
    date: string;
    amount: number;
    tickets: number;
  }>,
  sales_by_ticket_type: [] as Array<{
    name: string;
    tickets_sold: number;
    revenue: number;
  }>,
});

// Computed properties
const dateRangeText = computed(() => {
  switch (dateRange.value) {
    case "7":
      return "Last 7 days";
    case "30":
      return "Last 30 days";
    case "90":
      return "Last 90 days";
    case "365":
      return "Last year";
    case "all":
      return "All time";
    default:
      return "Last 30 days";
  }
});

// Chart data
const salesChartData = computed(() => {
  const labels = analytics.sales_by_day.map((day) => {
    const date = new Date(day.date);
    return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
  });

  const ticketData = analytics.sales_by_day.map((day) => day.tickets);

  return {
    labels,
    datasets: [
      {
        label: "Tickets Sold",
        data: ticketData,
        borderColor: "#4F46E5",
        backgroundColor: "rgba(79, 70, 229, 0.1)",
        borderWidth: 2,
        tension: 0.4,
        fill: true,
      },
    ],
  };
});

const salesChartOptions = {
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        precision: 0,
      },
    },
  },
  plugins: {
    tooltip: {
      callbacks: {
        label: (context: any) => `Tickets: ${context.raw}`,
      },
    },
  },
};

const revenueChartData = computed(() => {
  const labels = analytics.sales_by_day.map((day) => {
    const date = new Date(day.date);
    return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
  });

  const revenueData = analytics.sales_by_day.map((day) => day.amount);

  return {
    labels,
    datasets: [
      {
        label: "Revenue",
        data: revenueData,
        backgroundColor: "#10B981",
        borderColor: "#059669",
        borderWidth: 1,
      },
    ],
  };
});

const revenueChartOptions = {
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value: number) => `$${value}`,
      },
    },
  },
  plugins: {
    tooltip: {
      callbacks: {
        label: (context: any) => `Revenue: $${context.raw.toFixed(2)}`,
      },
    },
  },
};

const ticketTypeChartData = computed(() => {
  const labels = analytics.sales_by_ticket_type.map((type) => type.name);
  const data = analytics.sales_by_ticket_type.map((type) => type.tickets_sold);

  return {
    labels,
    datasets: [
      {
        data,
        backgroundColor: [
          "#4F46E5", // Indigo
          "#10B981", // Emerald
          "#F59E0B", // Amber
          "#EF4444", // Red
          "#8B5CF6", // Purple
          "#EC4899", // Pink
          "#06B6D4", // Cyan
          "#84CC16", // Lime
        ],
      },
    ],
  };
});

const ticketTypeChartOptions = {
  plugins: {
    legend: {
      position: "right",
    },
    tooltip: {
      callbacks: {
        label: (context: any) => {
          const label = context.label || "";
          const value = context.raw || 0;
          const total = context.dataset.data.reduce(
            (a: number, b: number) => a + b,
            0
          );
          const percentage = total ? Math.round((value / total) * 100) : 0;
          return `${label}: ${value} tickets (${percentage}%)`;
        },
      },
    },
  },
};

// Methods
const fetchEvents = async () => {
  try {
    const response = await organizerService.getEvents();
    events.value = response.data;
  } catch (err: any) {
    console.error("Error fetching events:", err);
    notificationStore.error("Failed to load events");
  }
};

const fetchAnalytics = async () => {
  loading.value = true;
  error.value = "";

  try {
    // Prepare params
    const params: Record<string, string> = {};

    if (dateRange.value !== "all") {
      const days = parseInt(dateRange.value);
      const startDate = new Date();
      startDate.setDate(startDate.getDate() - days);
      params.start_date = startDate.toISOString().split("T")[0];
    }

    // Fetch analytics data
    const data = await organizerService.getOrganizerStats(params);

    // Update analytics state
    Object.assign(analytics, data);
  } catch (err: any) {
    console.error("Error fetching analytics:", err);
    error.value = err.message || "Failed to load analytics data";
  } finally {
    loading.value = false;
  }
};

const fetchEventAnalytics = async () => {
  if (!selectedEventId.value) {
    // If no event is selected, fetch overall analytics
    return fetchAnalytics();
  }

  loading.value = true;
  error.value = "";

  try {
    // Prepare params
    const params: Record<string, string> = {};

    if (dateRange.value !== "all") {
      const days = parseInt(dateRange.value);
      const startDate = new Date();
      startDate.setDate(startDate.getDate() - days);
      params.start_date = startDate.toISOString().split("T")[0];
    }

    // Fetch event-specific analytics data
    const data = await organizerService.getEventStats(
      parseInt(selectedEventId.value),
      params
    );

    // Update analytics state with event-specific data
    // Note: This would need to be adjusted based on the actual API response structure
    // This is a placeholder implementation
    analytics.sales_summary = {
      total_sales: data.total_revenue || 0,
      total_tickets_sold: data.total_tickets_sold || 0,
      average_ticket_price: data.average_ticket_price || 0,
    };

    analytics.sales_by_day = data.sales_trends?.daily || [];
    analytics.sales_by_ticket_type = data.ticket_types || [];
  } catch (err: any) {
    console.error("Error fetching event analytics:", err);
    error.value = err.message || "Failed to load event analytics data";
  } finally {
    loading.value = false;
  }
};

const exportSalesData = async () => {
  isExporting.value = true;

  try {
    // Prepare params
    const params: Record<string, string> = {};

    if (dateRange.value !== "all") {
      const days = parseInt(dateRange.value);
      const startDate = new Date();
      startDate.setDate(startDate.getDate() - days);
      params.start_date = startDate.toISOString().split("T")[0];
    }

    if (selectedEventId.value) {
      params.event_id = selectedEventId.value;
    }

    // Call the API to get the sales data as a blob
    const blob = await organizerService.exportSalesData(params);

    // Create a download link and trigger the download
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = selectedEventId.value
      ? `sales-event-${selectedEventId.value}.csv`
      : `sales-${dateRange.value}-days.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    notificationStore.success("Sales data exported successfully");
  } catch (err: any) {
    console.error("Error exporting sales data:", err);
    notificationStore.error(err.message || "Failed to export sales data");
  } finally {
    isExporting.value = false;
  }
};

const exportAttendeeData = async () => {
  if (!selectedEventId.value) {
    notificationStore.error("Please select an event to export attendee data");
    return;
  }

  isExporting.value = true;

  try {
    // Call the API to get the attendee list as a blob
    const blob = await organizerService.exportAttendees(
      parseInt(selectedEventId.value)
    );

    // Create a download link and trigger the download
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `attendees-event-${selectedEventId.value}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    notificationStore.success("Attendee list exported successfully");
  } catch (err: any) {
    console.error("Error exporting attendee data:", err);
    notificationStore.error("Failed to export attendee data");
  } finally {
    isExporting.value = false;
  }
};

const formatNumber = (value: number) => {
  return new Intl.NumberFormat("en-US", { maximumFractionDigits: 2 }).format(
    value
  );
};

// Lifecycle hooks
onMounted(async () => {
  await Promise.all([fetchEvents(), fetchAnalytics()]);
});
</script>

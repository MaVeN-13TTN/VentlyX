<template>
  <div class="container mx-auto px-4 py-8">
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6"
    >
      <div>
        <h1 class="text-2xl font-bold mb-2">Attendees</h1>
        <p class="text-gray-600">Manage attendees for your events.</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <select
          v-model="selectedEventId"
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
          @change="fetchAttendees"
        >
          <option value="">Select an event</option>
          <option v-for="event in events" :key="event.id" :value="event.id">
            {{ event.title }}
          </option>
        </select>
      </div>
    </div>

    <!-- No Event Selected State -->
    <div
      v-if="!selectedEventId"
      class="bg-white rounded-lg shadow-md p-12 text-center"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-16 w-16 mx-auto text-gray-400 mb-4"
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
      <h3 class="text-lg font-medium text-gray-900 mb-2">No event selected</h3>
      <p class="text-gray-500 mb-6">
        Please select an event to view its attendees.
      </p>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="flex justify-center py-12">
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

    <!-- Attendees Content -->
    <div v-else>
      <!-- Search and Filters -->
      <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search attendees..."
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
              @input="handleSearch"
            />
          </div>
          <div class="flex gap-2">
            <select
              v-model="filters.ticketType"
              class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
              @change="fetchAttendees"
            >
              <option value="">All Ticket Types</option>
              <option
                v-for="type in ticketTypes"
                :key="type.id"
                :value="type.id"
              >
                {{ type.name }}
              </option>
            </select>
            <select
              v-model="filters.checkInStatus"
              class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
              @change="fetchAttendees"
            >
              <option value="">All Statuses</option>
              <option value="checked_in">Checked In</option>
              <option value="not_checked_in">Not Checked In</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Attendees List -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div v-if="attendees.length === 0" class="p-12 text-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-16 w-16 mx-auto text-gray-400 mb-4"
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
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            No attendees found
          </h3>
          <p class="text-gray-500">No attendees match your search criteria.</p>
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Attendee
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Ticket
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Purchase Date
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="attendee in attendees" :key="attendee.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ attendee.name }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ attendee.email }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ attendee.ticket_type }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ formatCurrency(attendee.ticket_price) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(attendee.purchase_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="
                    attendee.checked_in
                      ? 'bg-green-100 text-green-800'
                      : 'bg-yellow-100 text-yellow-800'
                  "
                >
                  {{ attendee.checked_in ? "Checked In" : "Not Checked In" }}
                </span>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <button
                  @click="toggleCheckIn(attendee)"
                  class="text-primary-600 hover:text-primary-900 mr-4"
                >
                  {{ attendee.checked_in ? "Undo Check-in" : "Check In" }}
                </button>
                <button
                  @click="viewDetails(attendee)"
                  class="text-gray-600 hover:text-gray-900"
                >
                  Details
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="attendees.length > 0"
        class="flex justify-between items-center"
      >
        <div class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{
            (meta.current_page - 1) * meta.per_page + 1
          }}</span>
          to
          <span class="font-medium">{{
            Math.min(meta.current_page * meta.per_page, meta.total)
          }}</span>
          of <span class="font-medium">{{ meta.total }}</span> attendees
        </div>
        <div class="flex space-x-2">
          <button
            @click="changePage(meta.current_page - 1)"
            :disabled="meta.current_page === 1"
            class="px-3 py-1 border rounded-md"
            :class="
              meta.current_page === 1
                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                : 'bg-white text-gray-700 hover:bg-gray-50'
            "
          >
            Previous
          </button>
          <button
            @click="changePage(meta.current_page + 1)"
            :disabled="meta.current_page === meta.last_page"
            class="px-3 py-1 border rounded-md"
            :class="
              meta.current_page === meta.last_page
                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                : 'bg-white text-gray-700 hover:bg-gray-50'
            "
          >
            Next
          </button>
        </div>
      </div>

      <!-- Export Section -->
      <div class="mt-6 flex justify-end">
        <button
          @click="exportAttendees"
          class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 flex items-center"
          :disabled="isExporting || attendees.length === 0"
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
    </div>

    <!-- Attendee Details Modal -->
    <div
      v-if="selectedAttendee"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Attendee Details</h3>
            <button
              @click="selectedAttendee = null"
              class="text-gray-500 hover:text-gray-700"
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
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <h4 class="text-sm font-medium text-gray-500">Name</h4>
              <p class="text-gray-900">{{ selectedAttendee.name }}</p>
            </div>

            <div>
              <h4 class="text-sm font-medium text-gray-500">Email</h4>
              <p class="text-gray-900">{{ selectedAttendee.email }}</p>
            </div>

            <div>
              <h4 class="text-sm font-medium text-gray-500">Ticket Type</h4>
              <p class="text-gray-900">{{ selectedAttendee.ticket_type }}</p>
            </div>

            <div>
              <h4 class="text-sm font-medium text-gray-500">Ticket Price</h4>
              <p class="text-gray-900">
                {{ formatCurrency(selectedAttendee.ticket_price) }}
              </p>
            </div>

            <div>
              <h4 class="text-sm font-medium text-gray-500">Purchase Date</h4>
              <p class="text-gray-900">
                {{ formatDate(selectedAttendee.purchase_date) }}
              </p>
            </div>

            <div>
              <h4 class="text-sm font-medium text-gray-500">Status</h4>
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="
                  selectedAttendee.checked_in
                    ? 'bg-green-100 text-green-800'
                    : 'bg-yellow-100 text-yellow-800'
                "
              >
                {{
                  selectedAttendee.checked_in ? "Checked In" : "Not Checked In"
                }}
              </span>
            </div>

            <div v-if="selectedAttendee.checked_in">
              <h4 class="text-sm font-medium text-gray-500">Check-in Time</h4>
              <p class="text-gray-900">
                {{ formatDate(selectedAttendee.check_in_time) }}
              </p>
            </div>

            <div v-if="selectedAttendee.notes">
              <h4 class="text-sm font-medium text-gray-500">Notes</h4>
              <p class="text-gray-900">{{ selectedAttendee.notes }}</p>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button
              @click="selectedAttendee = null"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
            >
              Close
            </button>
            <button
              @click="
                toggleCheckIn(selectedAttendee);
                selectedAttendee = null;
              "
              class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700"
            >
              {{ selectedAttendee.checked_in ? "Undo Check-in" : "Check In" }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from "vue";
import { useNotificationStore } from "@/stores/notification";
import organizerService, { OrganizerEvent } from "@/services/api/organizer";

// Initialize notification store
const notificationStore = useNotificationStore();

// State
const loading = ref(true);
const error = ref("");
const events = ref<OrganizerEvent[]>([]);
const selectedEventId = ref("");
const searchQuery = ref("");
const isExporting = ref(false);
const selectedAttendee = ref<Attendee | null>(null);

// Attendee interface
interface Attendee {
  id: number;
  name: string;
  email: string;
  ticket_type: string;
  ticket_price: number;
  purchase_date: string;
  checked_in: boolean;
  check_in_time?: string;
  notes?: string;
}

// Ticket types
const ticketTypes = ref<Array<{ id: number; name: string }>>([]);

// Attendees data
const attendees = ref<Attendee[]>([]);

// Pagination meta
const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

// Filters
const filters = reactive({
  search: "",
  ticketType: "",
  checkInStatus: "",
  page: 1,
  perPage: 10,
});

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

const fetchTicketTypes = async () => {
  if (!selectedEventId.value) return;

  try {
    const eventId = parseInt(selectedEventId.value);
    const ticketTypeData = await organizerService.getEventTicketTypes(eventId);

    // Transform the data to match our expected format
    ticketTypes.value = ticketTypeData.map((type) => ({
      id: type.id,
      name: type.name,
    }));
  } catch (err: any) {
    console.error("Error fetching ticket types:", err);
    notificationStore.error(err.message || "Failed to load ticket types");
  }
};

const fetchAttendees = async () => {
  if (!selectedEventId.value) return;

  loading.value = true;
  error.value = "";

  try {
    const eventId = parseInt(selectedEventId.value);

    // Prepare filters for API call
    const attendeeFilters = {
      eventId,
      search: filters.search,
      page: filters.page,
      perPage: filters.perPage,
    };

    // Add check-in status filter if selected
    if (filters.checkInStatus) {
      attendeeFilters.checkedIn = filters.checkInStatus === "checked_in";
    }

    // Add ticket type filter if selected
    if (filters.ticketType) {
      attendeeFilters.ticketType = filters.ticketType;
    }

    // Fetch attendees from API
    const response = await organizerService.getEventAttendees(attendeeFilters);

    // Update attendees and pagination meta
    attendees.value = response.data;
    meta.current_page = response.meta.current_page;
    meta.last_page = response.meta.last_page;
    meta.per_page = response.meta.per_page;
    meta.total = response.meta.total;
  } catch (err: any) {
    console.error("Error fetching attendees:", err);
    error.value = err.message || "Failed to load attendees";
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  // Debounce implementation would be better here
  filters.search = searchQuery.value;
  filters.page = 1; // Reset to first page on new search
  fetchAttendees();
};

const changePage = (page: number) => {
  if (page < 1 || page > meta.last_page) return;

  filters.page = page;
  fetchAttendees();
};

const toggleCheckIn = async (attendee: Attendee) => {
  if (!selectedEventId.value) return;

  try {
    const eventId = parseInt(selectedEventId.value);
    const newCheckInStatus = !attendee.checked_in;

    // Call API to update check-in status
    await organizerService.updateAttendeeCheckIn(
      eventId,
      attendee.id,
      newCheckInStatus
    );

    // Update local state
    attendee.checked_in = newCheckInStatus;

    if (newCheckInStatus) {
      attendee.check_in_time = new Date().toISOString();
      notificationStore.success(`${attendee.name} has been checked in`);
    } else {
      attendee.check_in_time = undefined;
      notificationStore.success(
        `Check-in for ${attendee.name} has been undone`
      );
    }
  } catch (err: any) {
    console.error("Error toggling check-in:", err);
    notificationStore.error(err.message || "Failed to update check-in status");
  }
};

const viewDetails = (attendee: Attendee) => {
  selectedAttendee.value = { ...attendee };
};

const exportAttendees = async () => {
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

const formatDate = (dateString?: string) => {
  if (!dateString) return "N/A";

  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(amount);
};

// Watch for event selection changes
const handleEventChange = async () => {
  if (selectedEventId.value) {
    // Reset filters and pagination
    filters.search = "";
    searchQuery.value = "";
    filters.ticketType = "";
    filters.checkInStatus = "";
    filters.page = 1;

    // Fetch ticket types and attendees
    await fetchTicketTypes();
    await fetchAttendees();
  } else {
    // Clear attendees when no event is selected
    attendees.value = [];
    ticketTypes.value = [];
  }
};

// Lifecycle hooks
onMounted(async () => {
  await fetchEvents();
});

// Watch for event selection changes
watch(selectedEventId, handleEventChange);
</script>

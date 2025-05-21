<template>
  <div class="container mx-auto px-4 py-8">
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6"
    >
      <div>
        <h1 class="text-2xl font-bold mb-2">My Events</h1>
        <p class="text-gray-600">Manage your events here.</p>
      </div>
      <router-link
        :to="{ name: 'event-create' }"
        class="mt-4 sm:mt-0 bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 inline-flex items-center"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 mr-2"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
            clip-rule="evenodd"
          />
        </svg>
        Create New Event
      </router-link>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search events..."
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
            @input="handleSearch"
          />
        </div>
        <div class="flex gap-2">
          <select
            v-model="filters.status"
            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
            @change="fetchEvents"
          >
            <option value="">All Statuses</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <select
            v-model="filters.sortBy"
            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
            @change="fetchEvents"
          >
            <option value="start_time">Date</option>
            <option value="title">Title</option>
            <option value="tickets_sold">Tickets Sold</option>
            <option value="revenue">Revenue</option>
          </select>
          <select
            v-model="filters.sortOrder"
            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
            @change="fetchEvents"
          >
            <option value="desc">Descending</option>
            <option value="asc">Ascending</option>
          </select>
        </div>
      </div>
    </div>

    <!-- View Toggle -->
    <div class="flex justify-end mb-4">
      <div class="inline-flex rounded-md shadow-sm" role="group">
        <button
          type="button"
          class="px-4 py-2 text-sm font-medium rounded-l-lg border"
          :class="
            currentView === 'grid'
              ? 'bg-primary-600 text-white border-primary-600'
              : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
          "
          @click="currentView = 'grid'"
        >
          <svg
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
              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
            />
          </svg>
        </button>
        <button
          type="button"
          class="px-4 py-2 text-sm font-medium rounded-r-lg border"
          :class="
            currentView === 'list'
              ? 'bg-primary-600 text-white border-primary-600'
              : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
          "
          @click="currentView = 'list'"
        >
          <svg
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
              d="M4 6h16M4 10h16M4 14h16M4 18h16"
            />
          </svg>
        </button>
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

    <!-- Empty State -->
    <div
      v-else-if="events.length === 0"
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
          d="M12 6v6m0 0v6m0-6h6m-6 0H6"
        />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No events found</h3>
      <p class="text-gray-500 mb-6">
        Get started by creating your first event.
      </p>
      <router-link
        :to="{ name: 'event-create' }"
        class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700"
      >
        Create New Event
      </router-link>
    </div>

    <!-- Grid View -->
    <div
      v-else-if="currentView === 'grid'"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <!-- Event Cards -->
      <div
        v-for="event in events"
        :key="event.id"
        class="bg-white rounded-lg shadow-md overflow-hidden"
      >
        <div class="relative h-48">
          <img
            v-if="event.image_url"
            :src="event.image_url"
            :alt="event.title"
            class="w-full h-full object-cover"
          />
          <div
            v-else
            class="w-full h-full bg-gray-200 flex items-center justify-center"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-12 w-12 text-gray-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
          </div>
          <div class="absolute top-2 right-2">
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="{
                'bg-green-100 text-green-800': event.status === 'published',
                'bg-yellow-100 text-yellow-800': event.status === 'draft',
                'bg-red-100 text-red-800': event.status === 'cancelled',
              }"
            >
              {{ event.status.charAt(0).toUpperCase() + event.status.slice(1) }}
            </span>
          </div>
        </div>
        <div class="p-4">
          <h3 class="text-lg font-semibold mb-2 truncate">{{ event.title }}</h3>
          <p class="text-gray-500 text-sm mb-4 line-clamp-2">
            {{ event.description }}
          </p>
          <div class="flex items-center text-sm text-gray-500 mb-4">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 mr-1"
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
            {{ formatDate(event.start_time) }}
          </div>
          <div class="flex items-center justify-between">
            <div>
              <span class="text-sm font-medium"
                >{{ event.tickets_sold }} tickets sold</span
              >
            </div>
            <div class="flex space-x-2">
              <router-link
                :to="{ name: 'event-edit', params: { id: event.id } }"
                class="p-2 text-blue-600 hover:bg-blue-50 rounded-md"
              >
                <svg
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
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
              </router-link>
              <button
                @click="duplicateEvent(event.id)"
                class="p-2 text-green-600 hover:bg-green-50 rounded-md"
              >
                <svg
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
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                  />
                </svg>
              </button>
              <button
                @click="confirmDeleteEvent(event)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-md"
              >
                <svg
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
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- List View -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
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
              Date
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Status
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Tickets
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Revenue
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
          <tr v-for="event in events" :key="event.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <img
                    v-if="event.image_url"
                    class="h-10 w-10 rounded-full object-cover"
                    :src="event.image_url"
                    alt=""
                  />
                  <div
                    v-else
                    class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-6 w-6 text-gray-400"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                      />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ event.title }}
                  </div>
                  <div class="text-sm text-gray-500 truncate max-w-xs">
                    {{ event.location }}
                  </div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                {{ formatDate(event.start_time) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="{
                  'bg-green-100 text-green-800': event.status === 'published',
                  'bg-yellow-100 text-yellow-800': event.status === 'draft',
                  'bg-red-100 text-red-800': event.status === 'cancelled',
                }"
              >
                {{
                  event.status.charAt(0).toUpperCase() + event.status.slice(1)
                }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ event.tickets_sold }} /
              {{ event.tickets_available + event.tickets_sold }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              ${{ event.revenue.toFixed(2) }}
            </td>
            <td
              class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
            >
              <div class="flex justify-end space-x-2">
                <router-link
                  :to="{ name: 'event-edit', params: { id: event.id } }"
                  class="text-blue-600 hover:text-blue-900"
                >
                  Edit
                </router-link>
                <button
                  @click="duplicateEvent(event.id)"
                  class="text-green-600 hover:text-green-900"
                >
                  Duplicate
                </button>
                <button
                  @click="confirmDeleteEvent(event)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div
      v-if="events.length > 0"
      class="flex justify-between items-center mt-6"
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
        of <span class="font-medium">{{ meta.total }}</span> events
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

    <!-- Delete Confirmation Modal -->
    <!-- We'll implement this later -->
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from "vue";
import { useRouter } from "vue-router";
import organizerService, {
  OrganizerEvent,
  EventFilters,
  EventResponse,
} from "@/services/api/organizer";
import { useNotificationStore } from "@/stores/notification";

// Initialize router and notification store
const router = useRouter();
const notificationStore = useNotificationStore();

// State
const events = ref<OrganizerEvent[]>([]);
const loading = ref(true);
const error = ref("");
const currentView = ref<"grid" | "list">("grid");
const searchQuery = ref("");
const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const filters = reactive<EventFilters>({
  status: "",
  search: "",
  page: 1,
  perPage: 10,
  sortBy: "start_time",
  sortOrder: "desc",
});

// Methods
const fetchEvents = async () => {
  loading.value = true;
  error.value = "";

  try {
    const response: EventResponse = await organizerService.getEvents(filters);
    events.value = response.data;

    // Update pagination meta
    meta.current_page = response.meta.current_page;
    meta.last_page = response.meta.last_page;
    meta.per_page = response.meta.per_page;
    meta.total = response.meta.total;
  } catch (err: any) {
    console.error("Error fetching events:", err);
    error.value = err.message || "Failed to load events";
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  // Debounce implementation would be better here
  filters.search = searchQuery.value;
  filters.page = 1; // Reset to first page on new search
  fetchEvents();
};

const changePage = (page: number) => {
  if (page < 1 || page > meta.last_page) return;

  filters.page = page;
  fetchEvents();
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const duplicateEvent = async (eventId: number) => {
  try {
    loading.value = true;
    const newEvent = await organizerService.duplicateEvent(eventId);
    notificationStore.success(`Event "${newEvent.title}" has been duplicated`);
    fetchEvents();
  } catch (err: any) {
    console.error("Error duplicating event:", err);
    notificationStore.error(err.message || "Failed to duplicate event");
  } finally {
    loading.value = false;
  }
};

const confirmDeleteEvent = async (event: OrganizerEvent) => {
  if (
    confirm(
      `Are you sure you want to delete the event "${event.title}"? This action cannot be undone.`
    )
  ) {
    try {
      loading.value = true;
      await organizerService.deleteEvent(event.id);
      notificationStore.success(`Event "${event.title}" has been deleted`);
      fetchEvents();
    } catch (err: any) {
      console.error("Error deleting event:", err);
      notificationStore.error(err.message || "Failed to delete event");
    } finally {
      loading.value = false;
    }
  }
};

// Lifecycle hooks
onMounted(() => {
  fetchEvents();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

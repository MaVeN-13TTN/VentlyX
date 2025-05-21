<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
      <router-link
        :to="{ name: 'organizer-events' }"
        class="mr-4 text-gray-500 hover:text-gray-700"
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
            d="M10 19l-7-7m0 0l7-7m-7 7h18"
          />
        </svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-bold">Create New Event</h1>
        <p class="text-gray-600">Fill in the details to create your event.</p>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Basic Information Section -->
        <div>
          <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">
            Basic Information
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Event Title -->
            <div class="col-span-2">
              <label
                for="title"
                class="block text-sm font-medium text-gray-700 mb-1"
                >Event Title *</label
              >
              <input
                type="text"
                id="title"
                v-model="eventData.title"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                :class="{ 'border-red-500': errors.title }"
                placeholder="Enter event title"
              />
              <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                {{ errors.title }}
              </p>
            </div>

            <!-- Event Description -->
            <div class="col-span-2">
              <label
                for="description"
                class="block text-sm font-medium text-gray-700 mb-1"
                >Description *</label
              >
              <textarea
                id="description"
                v-model="eventData.description"
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                :class="{ 'border-red-500': errors.description }"
                placeholder="Describe your event"
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description }}
              </p>
            </div>

            <!-- Event Category -->
            <div>
              <label
                for="category"
                class="block text-sm font-medium text-gray-700 mb-1"
                >Category *</label
              >
              <select
                id="category"
                v-model="eventData.category"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                :class="{ 'border-red-500': errors.category }"
              >
                <option value="">Select a category</option>
                <option value="Conference">Conference</option>
                <option value="Workshop">Workshop</option>
                <option value="Seminar">Seminar</option>
                <option value="Networking">Networking</option>
                <option value="Other">Other</option>
              </select>
              <p v-if="errors.category" class="mt-1 text-sm text-red-600">
                {{ errors.category }}
              </p>
            </div>

            <!-- Event Status -->
            <div>
              <label
                for="status"
                class="block text-sm font-medium text-gray-700 mb-1"
                >Status</label
              >
              <select
                id="status"
                v-model="eventData.status"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
              >
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </select>
              <p class="mt-1 text-xs text-gray-500">
                Draft events are not visible to the public
              </p>
            </div>
          </div>
        </div>

        <!-- Date and Time Section -->
        <div>
          <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">
            Date and Time
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Start Date and Time -->
            <div>
              <label
                for="start_time"
                class="block text-sm font-medium text-gray-700 mb-1"
                >Start Date and Time *</label
              >
              <input
                type="datetime-local"
                id="start_time"
                v-model="eventData.start_time"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                :class="{ 'border-red-500': errors.start_time }"
              />
              <p v-if="errors.start_time" class="mt-1 text-sm text-red-600">
                {{ errors.start_time }}
              </p>
            </div>

            <!-- End Date and Time -->
            <div>
              <label
                for="end_time"
                class="block text-sm font-medium text-gray-700 mb-1"
                >End Date and Time *</label
              >
              <input
                type="datetime-local"
                id="end_time"
                v-model="eventData.end_time"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                :class="{ 'border-red-500': errors.end_time }"
              />
              <p v-if="errors.end_time" class="mt-1 text-sm text-red-600">
                {{ errors.end_time }}
              </p>
            </div>
          </div>
        </div>

        <!-- Location Section -->
        <div>
          <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">
            Location
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Location -->
            <div>
              <label
                for="location"
                class="block text-sm font-medium text-gray-700 mb-1"
                >Location *</label
              >
              <input
                type="text"
                id="location"
                v-model="eventData.location"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                :class="{ 'border-red-500': errors.location }"
                placeholder="City, Country"
              />
              <p v-if="errors.location" class="mt-1 text-sm text-red-600">
                {{ errors.location }}
              </p>
            </div>

            <!-- Venue -->
            <div>
              <label
                for="venue"
                class="block text-sm font-medium text-gray-700 mb-1"
                >Venue *</label
              >
              <input
                type="text"
                id="venue"
                v-model="eventData.venue"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                :class="{ 'border-red-500': errors.venue }"
                placeholder="Venue name or address"
              />
              <p v-if="errors.venue" class="mt-1 text-sm text-red-600">
                {{ errors.venue }}
              </p>
            </div>
          </div>
        </div>

        <!-- Image Upload Section -->
        <div>
          <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">
            Event Image
          </h2>
          <div class="grid grid-cols-1 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Event Image</label
              >
              <div
                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
              >
                <div class="space-y-1 text-center">
                  <div v-if="imagePreview" class="mb-4">
                    <img
                      :src="imagePreview"
                      alt="Preview"
                      class="mx-auto h-40 object-cover"
                    />
                  </div>
                  <div v-else class="flex text-sm text-gray-600">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="mx-auto h-12 w-12 text-gray-400"
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
                  <div class="flex text-sm text-gray-600">
                    <label
                      for="image"
                      class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none"
                    >
                      <span>Upload an image</span>
                      <input
                        id="image"
                        name="image"
                        type="file"
                        class="sr-only"
                        accept="image/jpeg,image/png,image/jpg"
                        @change="handleImageUpload"
                      />
                    </label>
                    <p class="pl-1">or drag and drop</p>
                  </div>
                  <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 2MB</p>
                </div>
              </div>
              <p v-if="errors.image" class="mt-1 text-sm text-red-600">
                {{ errors.image }}
              </p>
            </div>
          </div>
        </div>

        <!-- Capacity Section -->
        <div>
          <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">
            Capacity
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label
                for="max_capacity"
                class="block text-sm font-medium text-gray-700 mb-1"
                >Maximum Capacity</label
              >
              <input
                type="number"
                id="max_capacity"
                v-model="eventData.max_capacity"
                min="1"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                :class="{ 'border-red-500': errors.max_capacity }"
                placeholder="Enter maximum number of attendees"
              />
              <p v-if="errors.max_capacity" class="mt-1 text-sm text-red-600">
                {{ errors.max_capacity }}
              </p>
              <p v-else class="mt-1 text-xs text-gray-500">
                Leave empty for unlimited capacity
              </p>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 pt-4">
          <router-link
            :to="{ name: 'organizer-events' }"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </router-link>
          <button
            type="submit"
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting" class="flex items-center">
              <svg
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              Creating...
            </span>
            <span v-else>Create Event</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import { useNotificationStore } from "@/stores/notification";
import organizerService from "@/services/api/organizer";

// Initialize router and notification store
const router = useRouter();
const notificationStore = useNotificationStore();

// Form state
const eventData = reactive({
  title: "",
  description: "",
  start_time: "",
  end_time: "",
  location: "",
  venue: "",
  category: "",
  status: "draft",
  max_capacity: "",
  image: null as File | null,
});

const errors = reactive({
  title: "",
  description: "",
  start_time: "",
  end_time: "",
  location: "",
  venue: "",
  category: "",
  max_capacity: "",
  image: "",
});

const isSubmitting = ref(false);
const imagePreview = ref<string | null>(null);

// Methods
const handleImageUpload = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (!input.files || input.files.length === 0) {
    eventData.image = null;
    imagePreview.value = null;
    return;
  }

  const file = input.files[0];

  // Validate file type
  if (!["image/jpeg", "image/png", "image/jpg"].includes(file.type)) {
    errors.image = "Please upload a valid image file (JPEG, JPG, or PNG)";
    eventData.image = null;
    imagePreview.value = null;
    return;
  }

  // Validate file size (2MB max)
  if (file.size > 2 * 1024 * 1024) {
    errors.image = "Image size should not exceed 2MB";
    eventData.image = null;
    imagePreview.value = null;
    return;
  }

  // Clear previous error
  errors.image = "";

  // Set file and create preview
  eventData.image = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target?.result as string;
  };
  reader.readAsDataURL(file);
};

const validateForm = () => {
  let isValid = true;

  // Reset errors
  Object.keys(errors).forEach((key) => {
    errors[key as keyof typeof errors] = "";
  });

  // Validate title
  if (!eventData.title.trim()) {
    errors.title = "Title is required";
    isValid = false;
  } else if (eventData.title.length > 255) {
    errors.title = "Title should not exceed 255 characters";
    isValid = false;
  }

  // Validate description
  if (!eventData.description.trim()) {
    errors.description = "Description is required";
    isValid = false;
  }

  // Validate category
  if (!eventData.category) {
    errors.category = "Please select a category";
    isValid = false;
  }

  // Validate start time
  if (!eventData.start_time) {
    errors.start_time = "Start date and time is required";
    isValid = false;
  } else {
    const startDate = new Date(eventData.start_time);
    const now = new Date();
    if (startDate <= now) {
      errors.start_time = "Start date must be in the future";
      isValid = false;
    }
  }

  // Validate end time
  if (!eventData.end_time) {
    errors.end_time = "End date and time is required";
    isValid = false;
  } else if (eventData.start_time) {
    const startDate = new Date(eventData.start_time);
    const endDate = new Date(eventData.end_time);
    if (endDate <= startDate) {
      errors.end_time = "End date must be after start date";
      isValid = false;
    }
  }

  // Validate location
  if (!eventData.location.trim()) {
    errors.location = "Location is required";
    isValid = false;
  } else if (eventData.location.length > 255) {
    errors.location = "Location should not exceed 255 characters";
    isValid = false;
  }

  // Validate venue
  if (!eventData.venue.trim()) {
    errors.venue = "Venue is required";
    isValid = false;
  } else if (eventData.venue.length > 255) {
    errors.venue = "Venue should not exceed 255 characters";
    isValid = false;
  }

  // Validate max capacity if provided
  if (
    eventData.max_capacity &&
    (isNaN(Number(eventData.max_capacity)) ||
      Number(eventData.max_capacity) < 1)
  ) {
    errors.max_capacity = "Maximum capacity must be a positive number";
    isValid = false;
  }

  return isValid;
};

const submitForm = async () => {
  if (!validateForm()) {
    // Scroll to the first error
    const firstError = document.querySelector(".border-red-500");
    if (firstError) {
      firstError.scrollIntoView({ behavior: "smooth", block: "center" });
    }
    return;
  }

  isSubmitting.value = true;

  try {
    // Create FormData object for file upload
    const formData = new FormData();
    formData.append("title", eventData.title);
    formData.append("description", eventData.description);
    formData.append("start_time", eventData.start_time);
    formData.append("end_time", eventData.end_time);
    formData.append("location", eventData.location);
    formData.append("venue", eventData.venue);
    formData.append("category", eventData.category);
    formData.append("status", eventData.status);

    if (eventData.max_capacity) {
      formData.append("max_capacity", eventData.max_capacity.toString());
    }

    if (eventData.image) {
      formData.append("image", eventData.image);
    }

    // Send request to create event
    await organizerService.createEvent(formData);

    notificationStore.success("Event created successfully");

    // Redirect to event management page
    router.push({ name: "organizer-events" });
  } catch (err: any) {
    console.error("Error creating event:", err);

    // Handle validation errors from the server
    if (
      err.response &&
      err.response.status === 422 &&
      err.response.data.errors
    ) {
      const serverErrors = err.response.data.errors;
      Object.keys(serverErrors).forEach((key) => {
        if (key in errors) {
          errors[key as keyof typeof errors] = serverErrors[key][0];
        }
      });

      // Scroll to the first error
      const firstError = document.querySelector(".border-red-500");
      if (firstError) {
        firstError.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    } else {
      notificationStore.error(
        err.response?.data?.message || "Failed to create event"
      );
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script>

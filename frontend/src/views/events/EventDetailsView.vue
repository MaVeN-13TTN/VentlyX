<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-2xl font-bold mb-6">Event Details</h1>
      <p class="text-gray-600 mb-4">This page will display detailed information about a specific event.</p>
      
      <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-500">Event details will be displayed here.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router';

const route = useRoute();
const eventId = route.params.id;

// Event details component logic will go here
</script>
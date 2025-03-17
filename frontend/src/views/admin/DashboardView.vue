<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>
    <p class="text-gray-600 mb-4">Welcome to the admin dashboard.</p>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-2">Users</h2>
        <p class="text-gray-500">User statistics will be displayed here.</p>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-2">Events</h2>
        <p class="text-gray-500">Event statistics will be displayed here.</p>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-2">System Status</h2>
        <p class="text-gray-500">System status information will be displayed here.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Admin dashboard component logic will go here
</script> 
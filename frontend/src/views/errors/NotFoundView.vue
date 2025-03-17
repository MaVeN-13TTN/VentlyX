<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-12">
    <div class="max-w-md w-full text-center">
      <h1 class="text-9xl font-bold text-primary-600">404</h1>
      <h2 class="text-2xl font-semibold text-gray-900 mt-4">Page Not Found</h2>
      <p class="text-gray-600 mt-2">The page you are looking for doesn't exist or has been moved.</p>
      <div class="mt-6">
        <router-link to="/" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
          Go back home
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Not found component logic will go here
</script> 
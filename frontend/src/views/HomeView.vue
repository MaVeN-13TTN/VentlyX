<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-primary to-secondary opacity-10"></div>
      <div class="container mx-auto px-4 py-24 relative">
        <div class="max-w-3xl">
          <h1 class="text-5xl md:text-6xl font-bold mb-6 text-text-light dark:text-text-dark">
            Find and Book Amazing Events
          </h1>
          <p class="text-xl mb-8 text-text-light/80 dark:text-text-dark/80">
            Discover events that match your interests, book tickets, and create unforgettable memories.
          </p>
          <div class="flex flex-wrap gap-4">
            <router-link 
              to="/events" 
              class="bg-primary text-white px-8 py-4 rounded-full font-medium hover:bg-primary-600 transition-all duration-300 shadow-lg hover:shadow-xl"
            >
              Browse Events
            </router-link>
            <router-link 
              to="/register" 
              class="bg-transparent border-2 border-primary text-primary dark:text-dark-primary px-8 py-4 rounded-full font-medium hover:bg-primary/10 transition-all duration-300"
            >
              Create Account
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Featured Events Section -->
    <div class="container mx-auto px-4 py-16">
      <h2 class="text-3xl font-bold mb-12 text-center text-text-light dark:text-text-dark">Featured Events</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Event Card -->
        <div class="bg-white dark:bg-background-dark/50 rounded-2xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300">
          <div class="h-48 bg-gradient-to-br from-accent-pink to-accent-blue"></div>
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-2 text-text-light dark:text-text-dark">Event Title</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <div class="flex justify-between items-center">
              <span class="text-primary dark:text-dark-primary font-medium">$25.00</span>
              <button class="bg-primary dark:bg-dark-primary text-white px-6 py-2 rounded-full hover:bg-primary-600 dark:hover:bg-dark-primary/90 transition-colors">
                View Details
              </button>
            </div>
          </div>
        </div>
        
        <!-- More Event Cards -->
        <div class="bg-white dark:bg-background-dark/50 rounded-2xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300">
          <div class="h-48 bg-gradient-to-br from-secondary to-accent-blue"></div>
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-2 text-text-light dark:text-text-dark">Event Title</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <div class="flex justify-between items-center">
              <span class="text-primary dark:text-dark-primary font-medium">$30.00</span>
              <button class="bg-primary dark:bg-dark-primary text-white px-6 py-2 rounded-full hover:bg-primary-600 dark:hover:bg-dark-primary/90 transition-colors">
                View Details
              </button>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-background-dark/50 rounded-2xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300">
          <div class="h-48 bg-gradient-to-br from-accent-pink to-secondary"></div>
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-2 text-text-light dark:text-text-dark">Event Title</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <div class="flex justify-between items-center">
              <span class="text-primary dark:text-dark-primary font-medium">$15.00</span>
              <button class="bg-primary dark:bg-dark-primary text-white px-6 py-2 rounded-full hover:bg-primary-600 dark:hover:bg-dark-primary/90 transition-colors">
                View Details
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- How It Works Section -->
    <div class="bg-white dark:bg-background-dark/30 py-16">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-16 text-center text-text-light dark:text-text-dark">How It Works</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
          <div class="text-center">
            <div class="bg-primary dark:bg-dark-primary text-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold shadow-lg">
              1
            </div>
            <h3 class="text-xl font-semibold mb-3 text-text-light dark:text-text-dark">Find Events</h3>
            <p class="text-text-light/70 dark:text-text-dark/70">Browse through our curated list of events or search for specific ones.</p>
          </div>
          
          <div class="text-center">
            <div class="bg-primary dark:bg-dark-primary text-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold shadow-lg">
              2
            </div>
            <h3 class="text-xl font-semibold mb-3 text-text-light dark:text-text-dark">Book Tickets</h3>
            <p class="text-text-light/70 dark:text-text-dark/70">Select your preferred tickets and complete the secure payment process.</p>
          </div>
          
          <div class="text-center">
            <div class="bg-primary dark:bg-dark-primary text-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold shadow-lg">
              3
            </div>
            <h3 class="text-xl font-semibold mb-3 text-text-light dark:text-text-dark">Attend & Enjoy</h3>
            <p class="text-text-light/70 dark:text-text-dark/70">Receive your tickets via email and get ready for an amazing experience.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Home view component logic will go here
</script> 
<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
      <!-- Hero background with animated gradient -->
      <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-secondary/20 dark:from-primary/10 dark:to-secondary/10"></div>
      <div class="absolute inset-0 opacity-20">
        <div class="absolute -inset-[10%] top-1/3 left-2/3 w-96 h-96 bg-accent-pink rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
        <div class="absolute -inset-[10%] top-1/4 right-2/3 w-96 h-96 bg-secondary rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
        <div class="absolute -inset-[10%] bottom-1/3 left-1/3 w-96 h-96 bg-accent-blue rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
      </div>
      
      <div class="container mx-auto py-28 lg:py-32 relative center-content">
        <div class="text-center max-w-3xl">
          <h1 class="text-5xl lg:text-6xl font-bold mb-6 text-text-light dark:text-text-dark leading-tight">
            Find and Book<br/><span class="bg-gradient-to-r from-primary to-accent-pink bg-clip-text text-transparent">Amazing Events</span>
          </h1>
          <p class="text-xl mb-8 text-text-light/80 dark:text-text-dark/80 max-w-2xl">
            Discover events that match your interests, book tickets, and create unforgettable memories with VentlyX.
          </p>
          <div class="flex flex-wrap gap-4 justify-center">
            <router-link 
              to="/events" 
              class="group bg-gradient-to-r from-primary to-secondary text-white px-8 py-4 rounded-lg font-medium hover:shadow-lg transition-all duration-300 flex items-center space-x-2"
            >
              <span>Browse Events</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </router-link>
            <router-link 
              to="/register" 
              class="bg-transparent border-2 border-primary text-primary dark:text-dark-primary dark:border-dark-primary px-8 py-4 rounded-lg font-medium hover:bg-primary/5 dark:hover:bg-dark-primary/5 transition-all duration-300"
            >
              Create Account
            </router-link>
          </div>
        </div>
        
        <!-- Abstract decorative element -->
        <div class="hidden lg:block absolute right-10 top-1/2 -translate-y-1/2">
          <div class="relative w-80 h-80">
            <div class="absolute inset-0 rounded-full bg-gradient-to-br from-secondary/30 to-accent-pink/30 animate-pulse"></div>
            <div class="absolute inset-4 rounded-full bg-gradient-to-tr from-primary/30 to-accent-blue/30 animate-pulse animation-delay-1000"></div>
            <div class="absolute inset-8 rounded-full bg-white dark:bg-background-dark/80 flex items-center justify-center">
              <div class="text-5xl font-bold bg-gradient-to-r from-primary to-accent-pink bg-clip-text text-transparent">
                VX
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Featured Events Section -->
    <div class="container mx-auto py-24">
      <div class="flex flex-col md:flex-row justify-between items-center mb-12">
        <h2 class="text-3xl font-bold text-text-light dark:text-text-dark mb-4 md:mb-0">
          <span class="border-b-4 border-primary dark:border-dark-primary pb-2">Featured Events</span>
        </h2>
        <router-link to="/events" class="text-primary dark:text-dark-primary hover:underline flex items-center space-x-1 font-medium">
          <span>View all events</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </router-link>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Event Card 1 -->
        <div class="group bg-white dark:bg-background-dark/50 rounded-xl shadow-md overflow-hidden transform hover:-translate-y-2 transition-all duration-300 border border-gray-100 dark:border-gray-800">
          <div class="relative h-48 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-pink to-accent-blue group-hover:scale-110 transition-transform duration-500"></div>
            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              <span class="bg-black/60 text-white px-4 py-2 rounded-lg">View Details</span>
            </div>
          </div>
          <div class="p-6">
            <div class="flex items-center space-x-2 mb-3">
              <span class="text-xs bg-primary/10 text-primary dark:bg-dark-primary/10 dark:text-dark-primary px-2 py-1 rounded-full">Music</span>
              <span class="text-xs text-gray-500 dark:text-gray-400">June 15, 2023</span>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-text-light dark:text-text-dark">Summer Music Festival</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 mb-4 line-clamp-2">Experience an unforgettable night of live music performances from top artists.</p>
            <div class="flex justify-between items-center">
              <span class="text-primary dark:text-dark-primary font-medium">$25.00</span>
              <button class="bg-primary dark:bg-dark-primary text-white px-4 py-2 rounded-lg hover:shadow-md transition-shadow duration-200">
                Book Now
              </button>
            </div>
          </div>
        </div>
        
        <!-- Event Card 2 -->
        <div class="group bg-white dark:bg-background-dark/50 rounded-xl shadow-md overflow-hidden transform hover:-translate-y-2 transition-all duration-300 border border-gray-100 dark:border-gray-800">
          <div class="relative h-48 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-secondary to-accent-blue group-hover:scale-110 transition-transform duration-500"></div>
            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              <span class="bg-black/60 text-white px-4 py-2 rounded-lg">View Details</span>
            </div>
          </div>
          <div class="p-6">
            <div class="flex items-center space-x-2 mb-3">
              <span class="text-xs bg-secondary/10 text-secondary px-2 py-1 rounded-full">Conference</span>
              <span class="text-xs text-gray-500 dark:text-gray-400">July 22, 2023</span>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-text-light dark:text-text-dark">Tech Innovation Summit</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 mb-4 line-clamp-2">Connect with industry leaders and discover the latest technological innovations.</p>
            <div class="flex justify-between items-center">
              <span class="text-primary dark:text-dark-primary font-medium">$30.00</span>
              <button class="bg-primary dark:bg-dark-primary text-white px-4 py-2 rounded-lg hover:shadow-md transition-shadow duration-200">
                Book Now
              </button>
            </div>
          </div>
        </div>
        
        <!-- Event Card 3 -->
        <div class="group bg-white dark:bg-background-dark/50 rounded-xl shadow-md overflow-hidden transform hover:-translate-y-2 transition-all duration-300 border border-gray-100 dark:border-gray-800">
          <div class="relative h-48 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-pink to-secondary group-hover:scale-110 transition-transform duration-500"></div>
            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              <span class="bg-black/60 text-white px-4 py-2 rounded-lg">View Details</span>
            </div>
          </div>
          <div class="p-6">
            <div class="flex items-center space-x-2 mb-3">
              <span class="text-xs bg-accent-pink/10 text-accent-pink px-2 py-1 rounded-full">Workshop</span>
              <span class="text-xs text-gray-500 dark:text-gray-400">August 5, 2023</span>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-text-light dark:text-text-dark">Creative Writing Workshop</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 mb-4 line-clamp-2">Enhance your storytelling skills with guidance from published authors.</p>
            <div class="flex justify-between items-center">
              <span class="text-primary dark:text-dark-primary font-medium">$15.00</span>
              <button class="bg-primary dark:bg-dark-primary text-white px-4 py-2 rounded-lg hover:shadow-md transition-shadow duration-200">
                Book Now
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- How It Works Section -->
    <div class="bg-white dark:bg-background-dark/50 py-24">
      <div class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold mb-16 text-center text-text-light dark:text-text-dark">
          <span class="relative">
            How It Works
            <span class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-gradient-to-r from-primary to-accent-pink"></span>
          </span>
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
          <!-- Step 1 -->
          <div class="relative text-center group">
            <!-- Connector line between steps (hidden on mobile) -->
            <div class="hidden md:block absolute top-10 left-full w-full h-0.5 bg-gradient-to-r from-primary/50 to-transparent -z-10 transform translate-x-4"></div>
            
            <div class="relative inline-flex mb-8">
              <!-- Icon background with animated border -->
              <div class="absolute inset-0 rounded-full bg-gradient-to-r from-primary to-secondary animate-spin-slow opacity-70"></div>
              <div class="relative bg-white dark:bg-background-dark w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold z-10 group-hover:scale-110 transition-transform duration-300">
                <span class="bg-gradient-to-r from-primary to-accent-pink bg-clip-text text-transparent">1</span>
              </div>
            </div>
            
            <h3 class="text-xl font-semibold mb-3 text-text-light dark:text-text-dark">Find Events</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 max-w-xs mx-auto">Browse our curated list of events or search for specific categories that match your interests.</p>
          </div>
          
          <!-- Step 2 -->
          <div class="relative text-center group">
            <!-- Connector line between steps (hidden on mobile) -->
            <div class="hidden md:block absolute top-10 left-full w-full h-0.5 bg-gradient-to-r from-primary/50 to-transparent -z-10 transform translate-x-4"></div>
            
            <div class="relative inline-flex mb-8">
              <!-- Icon background with animated border -->
              <div class="absolute inset-0 rounded-full bg-gradient-to-r from-secondary to-accent-blue animate-spin-slow opacity-70"></div>
              <div class="relative bg-white dark:bg-background-dark w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold z-10 group-hover:scale-110 transition-transform duration-300">
                <span class="bg-gradient-to-r from-secondary to-accent-blue bg-clip-text text-transparent">2</span>
              </div>
            </div>
            
            <h3 class="text-xl font-semibold mb-3 text-text-light dark:text-text-dark">Book Tickets</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 max-w-xs mx-auto">Select your preferred tickets and complete the secure payment process in just a few clicks.</p>
          </div>
          
          <!-- Step 3 -->
          <div class="relative text-center group">
            <div class="relative inline-flex mb-8">
              <!-- Icon background with animated border -->
              <div class="absolute inset-0 rounded-full bg-gradient-to-r from-accent-pink to-secondary animate-spin-slow opacity-70"></div>
              <div class="relative bg-white dark:bg-background-dark w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold z-10 group-hover:scale-110 transition-transform duration-300">
                <span class="bg-gradient-to-r from-accent-pink to-secondary bg-clip-text text-transparent">3</span>
              </div>
            </div>
            
            <h3 class="text-xl font-semibold mb-3 text-text-light dark:text-text-dark">Attend & Enjoy</h3>
            <p class="text-text-light/70 dark:text-text-dark/70 max-w-xs mx-auto">Receive your tickets via email, scan the QR code at the venue, and enjoy an amazing experience.</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- CTA Section -->
    <div class="py-20 relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-accent-pink/20 dark:from-primary/10 dark:to-accent-pink/10"></div>
      <div class="container mx-auto px-4 py-8 relative">
        <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-3xl font-bold mb-6 text-text-light dark:text-text-dark">Ready to Discover Amazing Events?</h2>
          <p class="text-xl mb-10 text-text-light/80 dark:text-text-dark/80">Join thousands of people who are already using VentlyX to find and attend the best events near them.</p>
          <router-link 
            to="/register" 
            class="inline-flex items-center bg-gradient-to-r from-primary to-accent-pink text-white px-8 py-4 rounded-lg font-medium hover:shadow-lg shadow-primary/20 transition-all duration-300"
          >
            <span>Get Started Today</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Home view component logic will go here
</script>

<style scoped>
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -30px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 10s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

.animate-spin-slow {
  animation: spin 8s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Hero Section -->
    <div class="relative overflow-hidden rounded-2xl">
      <!-- Gradient background -->
      <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-secondary/20 dark:from-primary/10 dark:to-secondary/10"></div>
      
      <div class="container mx-auto py-16 md:py-24 relative">
        <div class="text-center max-w-4xl mx-auto">
          <h1 class="text-4xl md:text-5xl font-bold mb-6 text-text-light dark:text-text-dark">
            About <span class="bg-gradient-to-r from-primary to-accent-pink bg-clip-text text-transparent">VentlyX</span>
          </h1>
          <p class="text-lg md:text-xl mb-8 text-text-light/80 dark:text-text-dark/80">
            We're on a mission to connect people through unforgettable events.
          </p>
        </div>
      </div>
    </div>

    <!-- Our Story Section -->
    <div class="container mx-auto py-16">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div>
          <h2 class="text-3xl font-bold mb-6 text-text-light dark:text-text-dark">
            <span class="border-b-4 border-primary dark:border-dark-primary pb-2">Our Story</span>
          </h2>
          <p class="text-text-light/80 dark:text-text-dark/80 mb-4">
            VentlyX was born out of a simple idea: make it easier for people to discover and attend events they'll love. Founded in 2023, we've quickly grown into a platform that connects thousands of event organizers with attendees across the globe.
          </p>
          <p class="text-text-light/80 dark:text-text-dark/80">
            Our team is passionate about creating meaningful connections through shared experiences. We believe that the right event can inspire, educate, and bring joy to people's lives.
          </p>
        </div>
        <div class="relative">
          <!-- Image placeholder with gradient -->
          <div class="rounded-2xl overflow-hidden shadow-xl h-80 bg-gradient-to-br from-primary/30 to-accent-pink/30 flex items-center justify-center">
            <div class="text-5xl font-bold bg-gradient-to-r from-primary to-accent-pink bg-clip-text text-transparent">
              VX
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Values Section -->
    <div class="container mx-auto px-4 py-20">
      <div class="container center-content">
        <h2 class="text-3xl font-bold mb-4 text-center text-text-light dark:text-text-dark">Our Values</h2>
        <p class="text-lg text-center text-text-light/70 dark:text-text-dark/70 mb-12 max-w-2xl">
          These core principles guide everything we do at VentlyX and shape our approach to connecting people through memorable events.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-4xl">
          <!-- Innovation -->
          <div class="group bg-white dark:bg-background-dark/30 rounded-xl p-6 border border-primary/10 dark:border-dark-primary/10 hover:border-primary/30 dark:hover:border-dark-primary/30 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="mb-5 w-14 h-14 rounded-xl bg-primary/10 dark:bg-dark-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-primary dark:text-dark-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-3 text-primary dark:text-dark-primary">Innovation</h3>
            <p class="text-text-light/70 dark:text-text-dark/70">We continuously strive to improve our platform with new features and technologies that enhance the event experience for organizers and attendees.</p>
          </div>
          
          <!-- Accessibility -->
          <div class="group bg-white dark:bg-background-dark/30 rounded-xl p-6 border border-primary/10 dark:border-dark-primary/10 hover:border-primary/30 dark:hover:border-dark-primary/30 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="mb-5 w-14 h-14 rounded-xl bg-primary/10 dark:bg-dark-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-primary dark:text-dark-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-3 text-primary dark:text-dark-primary">Accessibility</h3>
            <p class="text-text-light/70 dark:text-text-dark/70">We believe events should be accessible to everyone, regardless of ability or background, and design our platform to be inclusive.</p>
          </div>
          
          <!-- Community -->
          <div class="group bg-white dark:bg-background-dark/30 rounded-xl p-6 border border-primary/10 dark:border-dark-primary/10 hover:border-primary/30 dark:hover:border-dark-primary/30 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="mb-5 w-14 h-14 rounded-xl bg-primary/10 dark:bg-dark-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-primary dark:text-dark-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-3 text-primary dark:text-dark-primary">Community</h3>
            <p class="text-text-light/70 dark:text-text-dark/70">We foster connections between event organizers and attendees to build stronger communities and meaningful relationships.</p>
          </div>
          
          <!-- Reliability -->
          <div class="group bg-white dark:bg-background-dark/30 rounded-xl p-6 border border-primary/10 dark:border-dark-primary/10 hover:border-primary/30 dark:hover:border-dark-primary/30 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="mb-5 w-14 h-14 rounded-xl bg-primary/10 dark:bg-dark-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-primary dark:text-dark-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-3 text-primary dark:text-dark-primary">Reliability</h3>
            <p class="text-text-light/70 dark:text-text-dark/70">We provide a dependable platform that organizers and attendees can trust, ensuring smooth and secure event experiences.</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Team Section -->
    <div class="container center-content py-20">
      <div class="container center-content">
        <h2 class="text-3xl font-bold mb-4 text-center text-text-light dark:text-text-dark">Our Team</h2>
        <p class="text-lg text-center text-text-light/70 dark:text-text-dark/70 mb-16 max-w-2xl">
          Meet the passionate individuals behind VentlyX who work tirelessly to create the best event platform for you.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 w-full max-w-4xl">
          <!-- Team Member 1 -->
          <div class="group text-center">
            <div class="relative mb-6 mx-auto">
              <div class="absolute inset-0 rounded-full bg-gradient-to-br from-accent-pink to-accent-blue animate-spin-slow opacity-0 group-hover:opacity-70 transition-opacity duration-500"></div>
              <div class="relative z-10 w-40 h-40 bg-gradient-to-br from-accent-pink to-accent-blue rounded-full mx-auto overflow-hidden ring-4 ring-white dark:ring-background-dark">
                <!-- Replace with actual image if available -->
                <div class="w-full h-full flex items-center justify-center text-white text-4xl font-bold">S</div>
              </div>
            </div>
            <h3 class="text-xl font-semibold text-text-light dark:text-text-dark">Samuel</h3>
            <p class="text-primary dark:text-dark-primary font-medium mb-3">CEO & Co-Founder</p>
            <p class="text-text-light/70 dark:text-text-dark/70 max-w-xs mx-auto">Visionary leader with 10+ years experience in event management and technology.</p>
            
            <div class="flex justify-center space-x-3 mt-4">
              <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
                </svg>
              </a>
            </div>
          </div>
          
          <!-- Team Member 2 -->
          <div class="group text-center">
            <div class="relative mb-6 mx-auto">
              <div class="absolute inset-0 rounded-full bg-gradient-to-br from-secondary to-accent-blue animate-spin-slow opacity-0 group-hover:opacity-70 transition-opacity duration-500"></div>
              <div class="relative z-10 w-40 h-40 bg-gradient-to-br from-secondary to-accent-blue rounded-full mx-auto overflow-hidden ring-4 ring-white dark:ring-background-dark">
                <!-- Replace with actual image if available -->
                <div class="w-full h-full flex items-center justify-center text-white text-4xl font-bold">J</div>
              </div>
            </div>
            <h3 class="text-xl font-semibold text-text-light dark:text-text-dark">Joshua</h3>
            <p class="text-primary dark:text-dark-primary font-medium mb-3">CTO & Co-Founder</p>
            <p class="text-text-light/70 dark:text-text-dark/70 max-w-xs mx-auto">Tech enthusiast with expertise in scalable web applications and secure payment systems.</p>
            
            <div class="flex justify-center space-x-3 mt-4">
              <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M23.954 4.569c-.885.389-1.83.654-2.825.775 1.014-.611 1.794-1.574 2.163-2.723-.951.555-2.005.959-3.127 1.184-.896-.959-2.173-1.559-3.591-1.559-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124C7.691 8.094 4.066 6.13 1.64 3.161c-.427.722-.666 1.561-.666 2.475 0 1.71.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.314 0-.615-.03-.916-.086.631 1.953 2.445 3.377 4.604 3.417-1.68 1.319-3.809 2.105-6.102 2.105-.39 0-.779-.023-1.17-.067 2.189 1.394 4.768 2.209 7.557 2.209 9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63.961-.689 1.8-1.56 2.46-2.548l-.047-.02z"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.252.117-2.61 0 0 .828-.265 2.71 1.012a9.42 9.42 0 012.468-.332c.838.004 1.682.114 2.468.332 1.881-1.277 2.707-1.012 2.707-1.012.539 1.358.201 2.362.099 2.61.631.69 1.012 1.571 1.012 2.649 0 3.795-2.31 4.63-4.51 4.875.353.304.668.906.668 1.825 0 1.318-.012 2.381-.012 2.705 0 .264.18.57.682.474 3.924-1.324 6.748-5.033 6.748-9.386 0-5.468-4.369-9.837-9.837-9.837z"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c-5.468 0-9.837 4.369-9.837 9.837 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.194-.25-4.501-1.097-4.501-4.883 0-1.078.384-1.959 1.014-2.649-.102-.248-.44-1.252.097-2.61 0 0 .828-.265 2.71 1.012a9.42 9.42 0 012.468-.332c.838.004 1.682.114 2.468.332 1.881-1.277 2.707-1.012 2.707-1.012.539 1.358.201 2.362.099 2.61.631.69 1.012 1.571 1.012 2.649 0 3.795-2.31 4.63-4.51 4.875.353.304.668.906.668 1.825 0 1.318-.012 2.381-.012 2.705 0 .264.18.57.682.474 3.924-1.324 6.748-5.033 6.748-9.386 0-5.468-4.369-9.837-9.837-9.837z"/>
                </svg>
              </a>
            </div>
          </div>
          
          <!-- Team Member 3 -->
          <div class="group text-center">
            <div class="relative mb-6 mx-auto">
              <div class="absolute inset-0 rounded-full bg-gradient-to-br from-accent-pink to-secondary animate-spin-slow opacity-0 group-hover:opacity-70 transition-opacity duration-500"></div>
              <div class="relative z-10 w-40 h-40 bg-gradient-to-br from-accent-pink to-secondary rounded-full mx-auto overflow-hidden ring-4 ring-white dark:ring-background-dark">
                <!-- Replace with actual image if available -->
                <div class="w-full h-full flex items-center justify-center text-white text-4xl font-bold">W</div>
              </div>
            </div>
            <h3 class="text-xl font-semibold text-text-light dark:text-text-dark">Wesley</h3>
            <p class="text-primary dark:text-dark-primary font-medium mb-3">Head of Operations</p>
            <p class="text-text-light/70 dark:text-text-dark/70 max-w-xs mx-auto">Operations expert focused on creating seamless experiences for event organizers and attendees.</p>
            
            <div class="flex justify-center space-x-3 mt-4">
              <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-primary dark:hover:text-dark-primary transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// About view component logic will go here
</script>

<style scoped>
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
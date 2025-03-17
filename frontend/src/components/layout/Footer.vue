<template>
  <footer class="bg-white dark:bg-background-dark/50 border-t border-gray-100 dark:border-gray-800">
    <div class="container mx-auto px-6 py-14">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
        <!-- Brand Section -->
        <div class="space-y-4">
          <router-link to="/" class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center shadow-sm">
              <span class="text-white font-bold text-xl">V</span>
            </div>
            <span class="text-2xl font-bold text-primary dark:text-dark-primary">VentlyX</span>
          </router-link>
          <p class="text-text-light/80 dark:text-text-dark/80 text-base leading-relaxed">
            Connecting people with amazing events and experiences that create unforgettable memories.
          </p>
        </div>

        <!-- Quick Links -->
        <div>
          <h3 class="text-lg font-semibold mb-5 text-text-light dark:text-text-dark">Quick Links</h3>
          <ul class="space-y-3">
            <li v-for="item in quickLinks" :key="item.name">
              <router-link 
                :to="item.href"
                class="text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors text-base inline-block"
              >
                {{ item.name }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Support -->
        <div>
          <h3 class="text-lg font-semibold mb-5 text-text-light dark:text-text-dark">Support</h3>
          <ul class="space-y-3">
            <li v-for="item in supportLinks" :key="item.name">
              <router-link 
                :to="item.href"
                class="text-text-light/80 dark:text-text-dark/80 hover:text-primary dark:hover:text-dark-primary transition-colors text-base inline-block"
              >
                {{ item.name }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Social Links -->
        <div>
          <h3 class="text-lg font-semibold mb-5 text-text-light dark:text-text-dark">Connect With Us</h3>
          <div class="flex space-x-4">
            <a 
              href="#"
              target="_blank"
              rel="noopener noreferrer"
              class="w-11 h-11 rounded-full bg-background-light dark:bg-background-dark/30 flex items-center justify-center hover:bg-primary/10 dark:hover:bg-dark-primary/10 transition-colors shadow-sm"
              aria-label="Facebook"
            >
              <!-- Facebook Icon -->
              <svg class="w-6 h-6 text-primary dark:text-dark-primary" fill="currentColor" viewBox="0 0 24 24">
                <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
              </svg>
            </a>
            <a 
              href="#"
              target="_blank"
              rel="noopener noreferrer"
              class="w-11 h-11 rounded-full bg-background-light dark:bg-background-dark/30 flex items-center justify-center hover:bg-primary/10 dark:hover:bg-dark-primary/10 transition-colors shadow-sm"
              aria-label="Twitter"
            >
              <!-- Twitter Icon -->
              <svg class="w-6 h-6 text-primary dark:text-dark-primary" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
              </svg>
            </a>
            <a 
              href="#"
              target="_blank"
              rel="noopener noreferrer"
              class="w-11 h-11 rounded-full bg-background-light dark:bg-background-dark/30 flex items-center justify-center hover:bg-primary/10 dark:hover:bg-dark-primary/10 transition-colors shadow-sm"
              aria-label="Instagram"
            >
              <!-- Instagram Icon -->
              <svg class="w-6 h-6 text-primary dark:text-dark-primary" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
              </svg>
            </a>
            <a 
              href="#"
              target="_blank"
              rel="noopener noreferrer"
              class="w-11 h-11 rounded-full bg-background-light dark:bg-background-dark/30 flex items-center justify-center hover:bg-primary/10 dark:hover:bg-dark-primary/10 transition-colors shadow-sm"
              aria-label="LinkedIn"
            >
              <!-- LinkedIn Icon -->
              <svg class="w-6 h-6 text-primary dark:text-dark-primary" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
              </svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Newsletter Signup (Optional) -->
      <div class="mt-14 mb-10 bg-gradient-to-r from-primary/10 to-secondary/10 dark:from-primary/5 dark:to-secondary/5 p-8 rounded-2xl">
        <div class="max-w-2xl mx-auto text-center">
          <h3 class="text-xl font-semibold mb-4 text-text-light dark:text-text-dark">Stay Updated</h3>
          <p class="text-text-light/80 dark:text-text-dark/80 mb-6">Subscribe to our newsletter to get updates on the latest events and special promotions.</p>
          <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            <input 
              type="email" 
              placeholder="Your email address" 
              class="flex-grow px-4 py-3 rounded-full border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary bg-white dark:bg-background-dark/30"
            />
            <button 
              type="submit" 
              class="bg-primary dark:bg-dark-primary text-white px-6 py-3 rounded-full hover:bg-primary-600 dark:hover:bg-dark-primary/90 transition-colors shadow-sm"
            >
              Subscribe
            </button>
          </form>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="pt-8 border-t border-gray-100 dark:border-gray-800">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <p class="text-text-light/60 dark:text-text-dark/60 text-sm">
            © {{ new Date().getFullYear() }} VentlyX. All rights reserved.
          </p>
          <div class="flex flex-wrap justify-center gap-6">
            <router-link 
              v-for="item in legalLinks" 
              :key="item.name"
              :to="item.href"
              class="text-text-light/60 dark:text-text-dark/60 hover:text-primary dark:hover:text-dark-primary transition-colors text-sm"
            >
              {{ item.name }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
const quickLinks = [
  { name: 'Events', href: '/events' },
  { name: 'About Us', href: '/about' },
  { name: 'Contact', href: '/contact' },
  { name: 'Blog', href: '/blog' },
]

const supportLinks = [
  { name: 'Help Center', href: '/help' },
  { name: 'FAQs', href: '/faqs' },
  { name: 'Terms of Service', href: '/terms' },
  { name: 'Privacy Policy', href: '/privacy' },
]

const legalLinks = [
  { name: 'Terms of Service', href: '/terms' },
  { name: 'Privacy Policy', href: '/privacy' },
  { name: 'Cookie Policy', href: '/cookies' },
]
</script> 
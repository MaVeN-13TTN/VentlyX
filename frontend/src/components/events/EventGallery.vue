<template>
  <div class="mb-8 relative">
    <!-- Main Image -->
    <div 
      class="w-full h-96 rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-700 cursor-pointer"
      @click="openLightbox(selectedImageIndex)"
    >
      <img 
        v-if="images && images.length > 0" 
        :src="images[selectedImageIndex]" 
        :alt="altText" 
        class="w-full h-full object-cover"
      />
      <img 
        v-else-if="mainImage" 
        :src="mainImage" 
        :alt="altText" 
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <span class="text-gray-400 dark:text-gray-500">No image available</span>
      </div>
    </div>

    <!-- Thumbnail Navigation -->
    <div v-if="images && images.length > 1" class="flex mt-4 space-x-2 overflow-x-auto">
      <div 
        v-for="(image, index) in images" 
        :key="index"
        @click="selectedImageIndex = index"
        class="w-20 h-20 flex-shrink-0 rounded-md overflow-hidden cursor-pointer transition duration-200"
        :class="selectedImageIndex === index ? 'ring-2 ring-primary dark:ring-dark-primary' : 'opacity-70 hover:opacity-100'"
      >
        <img :src="image" :alt="`${altText} image ${index + 1}`" class="w-full h-full object-cover" />
      </div>
    </div>

    <!-- Lightbox -->
    <div v-if="lightboxOpen" class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center" @click="closeLightbox">
      <div class="relative max-w-6xl mx-auto" @click.stop>
        <!-- Close button -->
        <button 
          @click="closeLightbox" 
          class="absolute -top-12 right-0 text-white hover:text-gray-300"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Image -->
        <img 
          :src="images?.[lightboxIndex] || mainImage" 
          :alt="altText" 
          class="max-h-[80vh] max-w-full"
        />

        <!-- Navigation buttons -->
        <div v-if="images && images.length > 1" class="absolute inset-x-0 bottom-0 flex justify-between p-4">
          <button 
            @click.stop="prevImage" 
            class="bg-white/20 hover:bg-white/30 p-2 rounded-full"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button 
            @click.stop="nextImage" 
            class="bg-white/20 hover:bg-white/30 p-2 rounded-full"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

interface Props {
  images?: string[];
  mainImage?: string;
  altText: string;
}

const props = defineProps<Props>();

// State
const selectedImageIndex = ref(0);
const lightboxOpen = ref(false);
const lightboxIndex = ref(0);

// Methods
const openLightbox = (index: number) => {
  lightboxIndex.value = index;
  lightboxOpen.value = true;
  // Disable body scroll when lightbox is open
  document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
  lightboxOpen.value = false;
  // Re-enable body scroll
  document.body.style.overflow = '';
};

const nextImage = () => {
  if (!props.images || props.images.length <= 1) return;
  lightboxIndex.value = (lightboxIndex.value + 1) % props.images.length;
};

const prevImage = () => {
  if (!props.images || props.images.length <= 1) return;
  lightboxIndex.value = (lightboxIndex.value - 1 + props.images.length) % props.images.length;
};

// Close lightbox on escape key
const handleKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && lightboxOpen.value) {
    closeLightbox();
  } else if (event.key === 'ArrowRight' && lightboxOpen.value) {
    nextImage();
  } else if (event.key === 'ArrowLeft' && lightboxOpen.value) {
    prevImage();
  }
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
/* Optional: Add any component-specific styles here */
</style>
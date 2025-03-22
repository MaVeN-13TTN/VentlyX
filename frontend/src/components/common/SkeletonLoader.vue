<script setup lang="ts">
defineProps({
  type: {
    type: String,
    default: 'card',
    validator: (value: string) => ['card', 'list', 'text', 'image'].includes(value)
  },
  count: {
    type: [Number, String],
    default: 1
  },
  animated: {
    type: Boolean,
    default: true
  }
});

// Convert count to number for iteration
const getCount = () => {
  return typeof props.count === 'string' ? parseInt(props.count, 10) : props.count;
};
</script>

<template>
  <div>
    <!-- Card skeleton -->
    <div v-if="type === 'card'" class="space-y-6">
      <div 
        v-for="i in getCount()" 
        :key="i" 
        class="bg-white dark:bg-background-dark/50 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden"
      >
        <!-- Card image placeholder -->
        <div class="h-48 bg-gray-200 dark:bg-gray-700" :class="{ 'animate-pulse': animated }"></div>
        
        <!-- Card content -->
        <div class="p-6 space-y-4">
          <!-- Title placeholder -->
          <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4" :class="{ 'animate-pulse': animated }"></div>
          
          <!-- Description placeholder -->
          <div class="space-y-2">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded" :class="{ 'animate-pulse': animated }"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6" :class="{ 'animate-pulse': animated }"></div>
          </div>
          
          <!-- Footer placeholder -->
          <div class="flex justify-between items-center pt-2">
            <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-24" :class="{ 'animate-pulse': animated }"></div>
            <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-24" :class="{ 'animate-pulse': animated }"></div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- List skeleton -->
    <div v-else-if="type === 'list'" class="space-y-4">
      <div 
        v-for="i in getCount()" 
        :key="i" 
        class="bg-white dark:bg-background-dark/50 rounded-lg shadow-sm border border-gray-100 dark:border-gray-800 p-4 flex"
      >
        <!-- Avatar placeholder -->
        <div class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0 mr-4" :class="{ 'animate-pulse': animated }"></div>
        
        <!-- Content placeholder -->
        <div class="w-full space-y-2">
          <!-- Title placeholder -->
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3" :class="{ 'animate-pulse': animated }"></div>
          
          <!-- Description placeholder -->
          <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded" :class="{ 'animate-pulse': animated }"></div>
          <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-3/4" :class="{ 'animate-pulse': animated }"></div>
        </div>
      </div>
    </div>
    
    <!-- Text skeleton -->
    <div v-else-if="type === 'text'" class="space-y-2">
      <div 
        v-for="i in getCount()" 
        :key="i" 
        class="h-4 bg-gray-200 dark:bg-gray-700 rounded" 
        :class="{ 'animate-pulse': animated, 'w-3/4': i % 3 === 0, 'w-full': i % 3 !== 0 }"
      ></div>
    </div>
    
    <!-- Image skeleton -->
    <div v-else-if="type === 'image'">
      <div 
        class="bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden" 
        :class="{ 'animate-pulse': animated }"
        style="aspect-ratio: 16/9;"
      ></div>
    </div>
  </div>
</template>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>
<template>
  <div 
    :class="[
      'overflow-hidden', 
      animated ? 'animate-pulse' : '',
      typeClasses
    ]"
  >
    <div class="bg-gray-200 dark:bg-gray-700 h-full w-full"></div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'text',
    validator: (value: string) => ['text', 'image', 'card', 'circle', 'button', 'rectangle'].includes(value)
  },
  animated: {
    type: Boolean,
    default: true
  },
  height: {
    type: String,
    default: ''
  },
  width: {
    type: String,
    default: ''
  }
});

const typeClasses = computed(() => {
  const classes = {
    text: 'h-4 rounded',
    image: 'aspect-video rounded-lg',
    card: 'rounded-lg h-96',
    circle: 'rounded-full aspect-square',
    button: 'h-10 rounded-full',
    rectangle: 'h-32 rounded-lg'
  };
  
  let result = classes[props.type as keyof typeof classes];
  
  if (props.height) {
    result = result.replace(/h-\d+/, '');
    result += ` ${props.height}`;
  }
  
  if (props.width) {
    result = result.replace(/w-\d+/, '');
    result += ` ${props.width}`;
  }
  
  return result;
});
</script>

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
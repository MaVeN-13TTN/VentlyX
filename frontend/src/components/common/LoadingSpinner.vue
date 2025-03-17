<template>
  <div class="flex items-center justify-center space-x-2" :class="classes">
    <div class="spinner">
      <svg class="animate-spin h-5 w-5" :class="[sizeClass, colorClass]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
    <span v-if="props.text" :class="[textColorClass, textSizeClass]">{{ props.text }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  size?: 'sm' | 'md' | 'lg';
  color?: 'primary' | 'white';
  text?: string;
  fullscreen?: boolean;
  overlay?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  color: 'primary',
  text: '',
  fullscreen: false,
  overlay: false
});

const sizeClass = computed(() => {
  const sizes = {
    'sm': 'h-4 w-4',
    'md': 'h-6 w-6',
    'lg': 'h-8 w-8'
  };
  return sizes[props.size as keyof typeof sizes];
});

const colorClass = computed(() => {
  const colors = {
    'primary': 'text-primary-600',
    'white': 'text-white'
  };
  return colors[props.color as keyof typeof colors];
});

const textColorClass = computed(() => {
  const colors = {
    'primary': 'text-gray-700',
    'white': 'text-white'
  };
  return colors[props.color as keyof typeof colors];
});

const textSizeClass = computed(() => {
  const sizes = {
    'sm': 'text-sm',
    'md': 'text-base',
    'lg': 'text-lg'
  };
  return sizes[props.size as keyof typeof sizes];
});

const classes = computed(() => ({
  'fixed inset-0 bg-black bg-opacity-50 z-50': props.fullscreen && props.overlay,
  'fixed inset-0 z-50': props.fullscreen && !props.overlay
}));
</script>
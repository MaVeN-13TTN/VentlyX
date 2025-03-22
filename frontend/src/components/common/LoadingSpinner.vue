<template>
  <div 
    :class="[
      'animate-spin rounded-full border-t-transparent',
      spinnerClasses,
      borderColor
    ]"
    role="status"
    aria-label="Loading"
  >
    <span class="sr-only">Loading...</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  color?: 'primary' | 'secondary' | 'white' | 'gray';
  thickness?: 'normal' | 'thick';
}>(), {
  size: 'md',
  color: 'primary',
  thickness: 'normal'
});

// Classes for size and thickness
const spinnerClasses = computed(() => {
  const thicknessClass = {
    normal: 'border-2',
    thick: 'border-[3px]'
  }[props.thickness];

  switch (props.size) {
    case 'xs': return `${thicknessClass} h-3 w-3`;
    case 'sm': return `${thicknessClass} h-4 w-4`;
    case 'lg': return `${thicknessClass} h-8 w-8`;
    case 'xl': return `${thicknessClass} h-10 w-10`;
    default: return `${thicknessClass} h-6 w-6`; // md
  }
});

// Border color based on the color prop
const borderColor = computed(() => {
  switch (props.color) {
    case 'primary': return 'border-primary dark:border-dark-primary';
    case 'secondary': return 'border-secondary dark:border-dark-secondary';
    case 'white': return 'border-white';
    case 'gray': return 'border-gray-400 dark:border-gray-500';
    default: return 'border-primary dark:border-dark-primary';
  }
});
</script>

<style scoped>
:root {
  --color-primary: theme('colors.primary.DEFAULT');
  --color-secondary: theme('colors.secondary.DEFAULT');
}
</style>
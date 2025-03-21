<template>
  <div 
    :class="[
      'inline-block rounded-full animate-spin',
      sizeClasses
    ]"
    :style="{ borderColor: borderColor, borderTopColor: 'transparent' }"
    role="status"
    aria-label="Loading"
  ></div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value: string) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value: string) => ['primary', 'secondary', 'white', 'gray', 'black'].includes(value)
  },
  thickness: {
    type: String,
    default: 'normal',
    validator: (value: string) => ['thin', 'normal', 'thick'].includes(value)
  }
});

// Size classes
const sizeClasses = computed(() => {
  const thicknessClass = {
    thin: 'border',
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
    case 'primary': return 'var(--color-primary)';
    case 'secondary': return 'var(--color-secondary)';
    case 'white': return '#FFFFFF';
    case 'gray': return '#9CA3AF';
    case 'black': return '#000000';
    default: return 'var(--color-primary)';
  }
});
</script>

<style scoped>
:root {
  --color-primary: theme('colors.primary.DEFAULT');
  --color-secondary: theme('colors.secondary.DEFAULT');
}
</style>
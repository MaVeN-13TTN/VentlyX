<script setup lang="ts">
import { computed } from 'vue';
import LoadingSpinner from './LoadingSpinner.vue';

interface Props {
  type?: 'button' | 'submit' | 'reset';
  variant?: 'primary' | 'secondary' | 'outline' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  loading?: boolean;
  disabled?: boolean;
  block?: boolean;
  icon?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'button',
  variant: 'primary',
  size: 'md',
  loading: false,
  disabled: false,
  block: false,
  icon: false
});

const classes = computed(() => [
  'inline-flex items-center justify-center rounded-md font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors',
  {
    // Variants
    'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500': props.variant === 'primary',
    'bg-secondary-600 text-white hover:bg-secondary-700 focus:ring-secondary-500': props.variant === 'secondary',
    'border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-primary-500': props.variant === 'outline',
    'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500': props.variant === 'danger',
    
    // Sizes
    'px-2.5 py-1.5 text-xs': props.size === 'sm',
    'px-4 py-2 text-sm': props.size === 'md',
    'px-6 py-3 text-base': props.size === 'lg',
    
    // Block
    'w-full': props.block,
    
    // Icon only
    'p-2': props.icon,
    
    // Disabled state
    'opacity-50 cursor-not-allowed': props.disabled || props.loading
  }
]);
</script>

<template>
  <button
    :type="props.type"
    :disabled="props.disabled || props.loading"
    :class="classes"
  >
    <LoadingSpinner
      v-if="props.loading"
      size="sm"
      color="white"
      class="mr-2"
    />
    <slot />
  </button>
</template>
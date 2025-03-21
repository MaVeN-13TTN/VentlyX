<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center focus:outline-none transition-all duration-200 font-medium',
      sizeClasses,
      variantClasses,
      roundedClasses,
      iconOnlyClasses,
      blockClasses,
      disabled || loading ? 'opacity-60 cursor-not-allowed' : 'hover:brightness-110',
      className
    ]"
    @click="$emit('click', $event)"
  >
    <!-- Leading Icon -->
    <span v-if="$slots.icon && !iconRight" class="mr-2">
      <slot name="icon"></slot>
    </span>

    <!-- Loading Spinner (Left) -->
    <LoadingSpinner
      v-if="loading && !loadingRight"
      :size="loadingSize"
      :color="loadingColor || 'white'"
      class="mr-2"
    />

    <!-- Button Text -->
    <slot>{{ label }}</slot>

    <!-- Loading Spinner (Right) -->
    <LoadingSpinner
      v-if="loading && loadingRight"
      :size="loadingSize"
      :color="loadingColor || 'white'"
      class="ml-2"
    />

    <!-- Trailing Icon -->
    <span v-if="$slots.icon && iconRight" class="ml-2">
      <slot name="icon"></slot>
    </span>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { PropType } from 'vue';
import LoadingSpinner from './LoadingSpinner.vue';

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  type: {
    type: String as PropType<'button' | 'submit' | 'reset'>,
    default: 'button',
    validator: (value: string) => ['button', 'submit', 'reset'].includes(value)
  },
  variant: {
    type: String,
    default: 'filled',
    validator: (value: string) => ['filled', 'outlined', 'text', 'light', 'link'].includes(value)
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value: string) => [
      'primary', 'secondary', 'success', 'info', 'warning', 
      'danger', 'dark', 'light', 'white'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value: string) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  rounded: {
    type: String,
    default: 'md',
    validator: (value: string) => ['none', 'sm', 'md', 'lg', 'full'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  block: {
    type: Boolean,
    default: false
  },
  iconOnly: {
    type: Boolean,
    default: false
  },
  iconRight: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingRight: {
    type: Boolean,
    default: false
  },
  loadingSize: {
    type: String,
    default: 'sm'
  },
  loadingColor: {
    type: String,
    default: ''
  },
  className: {
    type: String,
    default: ''
  }
});

defineEmits(['click']);

// Size classes based on the size prop
const sizeClasses = computed(() => {
  if (props.iconOnly) {
    switch (props.size) {
      case 'xs': return 'h-6 w-6 p-1 text-xs';
      case 'sm': return 'h-8 w-8 p-1.5 text-sm';
      case 'lg': return 'h-12 w-12 p-3 text-lg';
      case 'xl': return 'h-14 w-14 p-3.5 text-xl';
      default: return 'h-10 w-10 p-2 text-base'; // md
    }
  }

  switch (props.size) {
    case 'xs': return 'px-2.5 py-1 text-xs';
    case 'sm': return 'px-3 py-1.5 text-sm';
    case 'lg': return 'px-5 py-2.5 text-lg';
    case 'xl': return 'px-6 py-3 text-xl';
    default: return 'px-4 py-2 text-base'; // md
  }
});

// Rounded classes based on the rounded prop
const roundedClasses = computed(() => {
  switch (props.rounded) {
    case 'none': return 'rounded-none';
    case 'sm': return 'rounded';
    case 'lg': return 'rounded-xl';
    case 'full': return 'rounded-full';
    default: return 'rounded-md'; // md
  }
});

// Variant + color classes
const variantClasses = computed(() => {
  const colorMap = {
    primary: {
      filled: 'bg-primary text-white dark:bg-dark-primary',
      outlined: 'border-2 border-primary text-primary dark:border-dark-primary dark:text-dark-primary',
      text: 'text-primary hover:bg-primary/10 dark:text-dark-primary dark:hover:bg-dark-primary/10',
      light: 'bg-primary-100 text-primary-700 dark:bg-primary-900/20 dark:text-primary-200',
      link: 'text-primary underline hover:no-underline p-0 dark:text-dark-primary'
    },
    secondary: {
      filled: 'bg-secondary text-white dark:bg-dark-secondary',
      outlined: 'border-2 border-secondary text-secondary dark:border-dark-secondary dark:text-dark-secondary',
      text: 'text-secondary hover:bg-secondary/10 dark:text-dark-secondary dark:hover:bg-dark-secondary/10',
      light: 'bg-secondary-100 text-secondary-700 dark:bg-secondary-900/20 dark:text-secondary-200',
      link: 'text-secondary underline hover:no-underline p-0 dark:text-dark-secondary'
    },
    success: {
      filled: 'bg-green-600 text-white',
      outlined: 'border-2 border-green-600 text-green-600',
      text: 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/10',
      light: 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-200',
      link: 'text-green-600 underline hover:no-underline p-0'
    },
    danger: {
      filled: 'bg-red-600 text-white',
      outlined: 'border-2 border-red-600 text-red-600',
      text: 'text-red-600 hover:bg-red-50 dark:hover:bg-red-900/10',
      light: 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-200',
      link: 'text-red-600 underline hover:no-underline p-0'
    },
    warning: {
      filled: 'bg-amber-500 text-white',
      outlined: 'border-2 border-amber-500 text-amber-500',
      text: 'text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/10',
      light: 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-200',
      link: 'text-amber-500 underline hover:no-underline p-0'
    },
    info: {
      filled: 'bg-blue-500 text-white',
      outlined: 'border-2 border-blue-500 text-blue-500',
      text: 'text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/10',
      light: 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-200',
      link: 'text-blue-500 underline hover:no-underline p-0'
    },
    dark: {
      filled: 'bg-gray-800 text-white dark:bg-gray-700',
      outlined: 'border-2 border-gray-800 text-gray-800 dark:border-gray-300 dark:text-gray-300',
      text: 'text-gray-800 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800',
      light: 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
      link: 'text-gray-800 underline hover:no-underline p-0 dark:text-gray-300'
    },
    light: {
      filled: 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
      outlined: 'border-2 border-gray-300 text-gray-700 dark:border-gray-600 dark:text-gray-300',
      text: 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800',
      light: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
      link: 'text-gray-700 underline hover:no-underline p-0 dark:text-gray-300'
    },
    white: {
      filled: 'bg-white text-gray-800 dark:bg-gray-800 dark:text-white',
      outlined: 'border-2 border-white text-white dark:border-gray-800 dark:text-gray-800',
      text: 'text-white hover:bg-white/10 dark:text-gray-800 dark:hover:bg-gray-200',
      light: 'bg-white/20 text-white dark:bg-gray-200 dark:text-gray-800',
      link: 'text-white underline hover:no-underline p-0 dark:text-gray-200'
    }
  };

  return colorMap[props.color as keyof typeof colorMap]?.[props.variant as keyof (typeof colorMap)['primary']] || 
    colorMap.primary[props.variant as keyof (typeof colorMap)['primary']];
});

// Icon only classes
const iconOnlyClasses = computed(() => {
  return props.iconOnly ? 'p-0 flex items-center justify-center' : '';
});

// Block classes
const blockClasses = computed(() => {
  return props.block ? 'w-full' : '';
});
</script>
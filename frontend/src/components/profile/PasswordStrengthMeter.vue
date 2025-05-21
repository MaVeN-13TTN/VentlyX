<template>
  <div class="mt-1">
    <div class="flex h-2 overflow-hidden bg-gray-200 dark:bg-gray-700 rounded">
      <div 
        class="transition-all duration-300 ease-out"
        :class="strengthColorClass"
        :style="{ width: `${(score / 5) * 100}%` }"
      ></div>
    </div>
    <div class="flex justify-between mt-1">
      <p class="text-xs text-gray-500 dark:text-gray-400">Strength: {{ strengthLabel }}</p>
      <p class="text-xs" :class="strengthTextColorClass">{{ strengthDescription }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps } from 'vue';

const props = defineProps({
  password: {
    type: String,
    required: true
  }
});

// Calculate password strength score (0-5)
const score = computed(() => {
  const password = props.password;
  if (!password) return 0;
  
  let score = 0;
  
  // Length check
  if (password.length >= 8) score += 1;
  if (password.length >= 12) score += 1;
  
  // Complexity checks
  if (/[A-Z]/.test(password)) score += 1;
  if (/[a-z]/.test(password)) score += 1;
  if (/[0-9]/.test(password)) score += 1;
  if (/[^A-Za-z0-9]/.test(password)) score += 1;
  
  // Cap at 5
  return Math.min(score, 5);
});

// Strength label based on score
const strengthLabel = computed(() => {
  const s = score.value;
  if (s === 0) return 'Very Weak';
  if (s === 1) return 'Weak';
  if (s === 2) return 'Fair';
  if (s === 3) return 'Good';
  if (s === 4) return 'Strong';
  return 'Very Strong';
});

// Color class based on score
const strengthColorClass = computed(() => {
  const s = score.value;
  if (s === 0) return 'bg-gray-300 dark:bg-gray-600';
  if (s === 1) return 'bg-red-500';
  if (s === 2) return 'bg-orange-500';
  if (s === 3) return 'bg-yellow-500';
  if (s === 4) return 'bg-green-500';
  return 'bg-green-600';
});

// Text color class based on score
const strengthTextColorClass = computed(() => {
  const s = score.value;
  if (s === 0) return 'text-gray-500 dark:text-gray-400';
  if (s === 1) return 'text-red-500';
  if (s === 2) return 'text-orange-500';
  if (s === 3) return 'text-yellow-500';
  if (s === 4) return 'text-green-500';
  return 'text-green-600';
});

// Description based on score
const strengthDescription = computed(() => {
  const s = score.value;
  if (s === 0) return 'Enter a password';
  if (s === 1) return 'Too weak';
  if (s === 2) return 'Could be stronger';
  if (s === 3) return 'Acceptable';
  if (s === 4) return 'Strong password';
  return 'Excellent password';
});
</script>

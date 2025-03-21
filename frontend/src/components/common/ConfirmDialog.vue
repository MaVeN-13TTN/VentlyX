<template>
  <BaseDialog 
    v-model="modelValue"
    :title="title"
    :size="size"
    :show-cancel-button="true" 
    :show-confirm-button="true"
    :cancel-text="cancelText"
    :confirm-text="confirmText"
    :confirm-disabled="loading"
    @confirm="handleConfirm"
  >
    <div class="text-text-light/80 dark:text-text-dark/80">
      <p v-if="message" class="mb-4">{{ message }}</p>
      <slot></slot>
    </div>
    
    <template #footer>
      <div class="flex justify-end gap-3">
        <BaseButton
          v-if="showCancel"
          variant="outline"
          size="md"
          :disabled="loading"
          @click="$emit('update:modelValue', false)"
        >
          {{ cancelText }}
        </BaseButton>
        
        <BaseButton
          v-if="showConfirm"
          :variant="type === 'danger' ? 'danger' : 'primary'"
          size="md"
          :loading="loading"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </BaseButton>
      </div>
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import BaseDialog from './BaseDialog.vue';
import BaseButton from './BaseButton.vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed with this action?'
  },
  type: {
    type: String,
    default: 'primary',
    validator: (value: string) => ['primary', 'danger'].includes(value)
  },
  size: {
    type: String,
    default: 'md'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  loading: {
    type: Boolean,
    default: false
  },
  showCancel: {
    type: Boolean,
    default: true
  },
  showConfirm: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['update:modelValue', 'confirm']);

const handleConfirm = () => {
  emit('confirm');
};
</script>
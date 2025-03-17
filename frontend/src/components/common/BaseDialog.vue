<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import BaseButton from './BaseButton.vue';

interface Props {
  modelValue: boolean;
  title: string;
  size?: 'sm' | 'md' | 'lg';
  hideClose?: boolean;
  preventClose?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  hideClose: false,
  preventClose: false
});

const emit = defineEmits(['update:modelValue', 'close']);

const maxWidth = computed(() => ({
  'sm': 'max-w-sm',
  'md': 'max-w-md',
  'lg': 'max-w-lg'
}[props.size]));

const handleClose = () => {
  if (!props.preventClose) {
    emit('update:modelValue', false);
    emit('close');
  }
};
</script>

<template>
  <TransitionRoot appear :show="props.modelValue" as="template">
    <Dialog as="div" class="relative z-50" @close="handleClose">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel :class="['w-full transform overflow-hidden rounded-lg bg-white p-6 text-left align-middle shadow-xl transition-all', maxWidth]">
              <div class="flex items-start justify-between">
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                  {{ props.title }}
                </DialogTitle>
                <button
                  v-if="!props.hideClose"
                  type="button"
                  class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                  @click="handleClose"
                >
                  <span class="sr-only">Close</span>
                  <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <div class="mt-3">
                <slot />
              </div>

              <div v-if="$slots.footer" class="mt-6 flex justify-end space-x-3">
                <slot name="footer" />
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
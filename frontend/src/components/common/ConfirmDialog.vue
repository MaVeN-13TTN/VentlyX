<script setup lang="ts">
import BaseDialog from './BaseDialog.vue';
import BaseButton from './BaseButton.vue';

interface Props {
  modelValue: boolean;
  title: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  confirmVariant?: 'primary' | 'danger';
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  confirmVariant: 'primary',
  loading: false
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

const handleConfirm = () => {
  emit('confirm');
};

const handleCancel = () => {
  emit('update:modelValue', false);
  emit('cancel');
};
</script>

<template>
  <BaseDialog
    v-model="props.modelValue"
    :title="props.title"
    :prevent-close="props.loading"
    @close="handleCancel"
  >
    <p class="text-sm text-gray-500">
      {{ props.message }}
    </p>

    <template #footer>
      <BaseButton
        variant="outline"
        :disabled="props.loading"
        @click="handleCancel"
      >
        {{ props.cancelText }}
      </BaseButton>
      <BaseButton
        :variant="props.confirmVariant"
        :loading="props.loading"
        :disabled="props.loading"
        @click="handleConfirm"
      >
        {{ props.confirmText }}
      </BaseButton>
    </template>
  </BaseDialog>
</template>
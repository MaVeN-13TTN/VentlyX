<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="id"
      class="block text-sm font-medium mb-1"
      :class="labelClass"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <div class="relative">
      <!-- Leading Icon -->
      <div
        v-if="icon"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"
      >
        <slot name="icon"></slot>
      </div>

      <!-- Input Element -->
      <input
        :id="id"
        :type="inputType"
        :value="modelValue"
        :name="name"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :autocomplete="autocomplete"
        :class="[
          'w-full border rounded-lg transition-colors duration-200 focus:outline-none',
          sizeClass,
          icon ? 'pl-10' : '',
          (clearable && modelValue) || type === 'password' ? 'pr-10' : '',
          errorMessage
            ? 'border-red-300 focus:border-red-500 focus:ring focus:ring-red-200 dark:border-red-500/30 dark:focus:border-red-500 dark:focus:ring-red-500/20'
            : 'border-gray-300 focus:border-primary focus:ring focus:ring-primary/20 dark:border-gray-700 dark:focus:border-dark-primary dark:focus:ring-dark-primary/20',
          disabled
            ? 'bg-gray-100 text-gray-500 cursor-not-allowed dark:bg-gray-800'
            : 'bg-white dark:bg-gray-900',
          inputClass,
        ]"
        @input="
          $emit('update:modelValue', ($event.target as HTMLInputElement).value)
        "
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />

      <!-- Password Toggle Button -->
      <button
        v-if="type === 'password'"
        type="button"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none z-10"
        @click="togglePasswordVisibility"
        tabindex="-1"
      >
        <!-- Eye icon when password is hidden -->
        <svg
          v-if="!showPassword"
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
          />
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
          />
        </svg>
        <!-- Eye-off icon when password is shown -->
        <svg
          v-else
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
          />
        </svg>
      </button>

      <!-- Clear Button -->
      <div
        v-if="clearable && modelValue && type !== 'password'"
        class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer"
        @click="$emit('update:modelValue', '')"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 text-gray-400 hover:text-gray-600"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </div>
    </div>

    <!-- Helper Text or Error Message -->
    <div v-if="errorMessage || helperText" class="mt-1 text-sm">
      <p v-if="errorMessage" class="text-red-500">{{ errorMessage }}</p>
      <p v-else-if="helperText" class="text-gray-500 dark:text-gray-400">
        {{ helperText }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";

// For password toggle functionality
const showPassword = ref(false);
const inputType = ref("");

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: "",
  },
  id: {
    type: String,
    default: () => `input-${Math.random().toString(36).substring(2, 9)}`,
  },
  label: {
    type: String,
    default: "",
  },
  name: {
    type: String,
    default: "",
  },
  type: {
    type: String,
    default: "text",
  },
  placeholder: {
    type: String,
    default: "",
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  icon: {
    type: Boolean,
    default: false,
  },
  clearable: {
    type: Boolean,
    default: false,
  },
  autocomplete: {
    type: String,
    default: "off",
  },
  helperText: {
    type: String,
    default: "",
  },
  errorMessage: {
    type: String,
    default: "",
  },
  size: {
    type: String,
    default: "md",
    validator: (value: string) => ["sm", "md", "lg"].includes(value),
  },
  labelClass: {
    type: String,
    default: "text-gray-700 dark:text-gray-200",
  },
  inputClass: {
    type: String,
    default: "text-gray-900 dark:text-gray-100",
  },
});

defineEmits(["update:modelValue", "blur", "focus"]);

// Initialize input type and watch for changes
watch(
  () => props.type,
  (newType) => {
    inputType.value = newType;
  },
  { immediate: true }
);

// Toggle password visibility
const togglePasswordVisibility = (e: Event) => {
  if (props.type === "password") {
    showPassword.value = !showPassword.value;
    inputType.value = showPassword.value ? "text" : "password";
    // Prevent default to avoid form submission
    e.preventDefault();
  }
};

// Size class
const sizeClass = computed(() => {
  switch (props.size) {
    case "sm":
      return "py-1.5 px-3 text-sm";
    case "lg":
      return "py-3 px-4 text-base";
    default:
      return "py-2 px-4 text-sm"; // md
  }
});
</script>

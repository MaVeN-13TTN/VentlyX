<template>
  <div>
    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Delete Account</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      Once you delete your account, there is no going back. Please be certain.
    </p>
    
    <div class="mt-4">
      <button 
        @click="showConfirmation = true"
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
      >
        Delete Account
      </button>
    </div>
    
    <!-- Confirmation Modal -->
    <div v-if="showConfirmation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-medium text-red-600 dark:text-red-500 mb-4">Confirm Account Deletion</h3>
        
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          This action cannot be undone. This will permanently delete your account and remove your data from our servers.
        </p>
        
        <div class="mb-4">
          <label for="confirm-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Please enter your password to confirm
          </label>
          <input 
            type="password" 
            id="confirm-password"
            v-model="confirmPassword"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"
            placeholder="Your current password"
          />
          <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-500">{{ error }}</p>
        </div>
        
        <div class="mb-4">
          <label class="flex items-center">
            <input 
              type="checkbox" 
              v-model="confirmDelete"
              class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
            />
            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
              I understand that this action is permanent
            </span>
          </label>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button 
            @click="showConfirmation = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="deleteAccount"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
            :disabled="!confirmDelete || !confirmPassword || isDeleting"
            :class="{ 'opacity-50 cursor-not-allowed': !confirmDelete || !confirmPassword || isDeleting }"
          >
            <span v-if="isDeleting">Deleting...</span>
            <span v-else>Delete My Account</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, defineEmits } from 'vue';

const emit = defineEmits(['delete-account']);

const showConfirmation = ref(false);
const confirmPassword = ref('');
const confirmDelete = ref(false);
const error = ref('');
const isDeleting = ref(false);

const deleteAccount = async () => {
  if (!confirmDelete.value || !confirmPassword.value) {
    return;
  }
  
  error.value = '';
  isDeleting.value = true;
  
  try {
    // Emit the delete event with the confirmation password
    emit('delete-account', confirmPassword.value);
    
    // Reset the form (this will happen if the parent doesn't redirect)
    confirmPassword.value = '';
    confirmDelete.value = false;
    showConfirmation.value = false;
  } catch (err: any) {
    error.value = err.message || 'Failed to delete account';
  } finally {
    isDeleting.value = false;
  }
};
</script>

<template>
  <div class="flex flex-col items-center">
    <div class="relative group">
      <!-- Profile Image Preview -->
      <div class="h-32 w-32 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 border-4 border-white dark:border-gray-800 shadow-md">
        <img 
          v-if="imagePreview || currentImage" 
          :src="imagePreview || currentImage" 
          alt="Profile picture" 
          class="h-full w-full object-cover"
        />
        <div v-else class="h-full w-full flex items-center justify-center text-gray-400 dark:text-gray-500">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
      </div>
      
      <!-- Upload Button Overlay -->
      <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer" @click="triggerFileInput">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </div>
    </div>
    
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      Click to upload a profile picture
    </p>
    
    <!-- Hidden File Input -->
    <input 
      ref="fileInput"
      type="file" 
      accept="image/*" 
      class="hidden" 
      @change="handleFileChange"
    />
    
    <!-- Action Buttons -->
    <div v-if="imageFile" class="flex space-x-2 mt-4">
      <button 
        @click="uploadImage" 
        class="px-3 py-1 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors text-sm"
        :disabled="isUploading"
      >
        <span v-if="isUploading">Uploading...</span>
        <span v-else>Save</span>
      </button>
      <button 
        @click="cancelUpload" 
        class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm"
      >
        Cancel
      </button>
    </div>
  </div>
  
  <!-- Image Cropper Modal -->
  <div v-if="showCropper" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Crop Image</h3>
      
      <div class="relative h-64 overflow-hidden rounded-md">
        <!-- Cropper component would go here -->
        <!-- For simplicity, we're just showing the image preview -->
        <img 
          v-if="imagePreview" 
          :src="imagePreview" 
          alt="Preview" 
          class="max-w-full max-h-full object-contain"
        />
      </div>
      
      <div class="flex justify-end space-x-3 mt-4">
        <button 
          @click="showCropper = false" 
          class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
        >
          Cancel
        </button>
        <button 
          @click="applyCrop" 
          class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors"
        >
          Apply
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, defineProps, defineEmits } from 'vue';

const props = defineProps({
  currentImage: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:image', 'upload-success', 'upload-error']);

const fileInput = ref<HTMLInputElement | null>(null);
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const isUploading = ref(false);
const showCropper = ref(false);

const triggerFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click();
  }
};

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    imageFile.value = target.files[0];
    
    // Create preview URL
    const reader = new FileReader();
    reader.onload = (e) => {
      if (e.target?.result) {
        imagePreview.value = e.target.result as string;
        
        // In a real implementation, we would show the cropper here
        // showCropper.value = true;
        
        // For now, we'll just emit the file directly
        emit('update:image', imageFile.value);
      }
    };
    reader.readAsDataURL(imageFile.value);
  }
};

const applyCrop = () => {
  // In a real implementation, this would apply the cropping
  // and update the imagePreview with the cropped image
  showCropper.value = false;
};

const uploadImage = async () => {
  if (!imageFile.value) return;
  
  isUploading.value = true;
  
  try {
    // Emit the file for the parent component to handle the upload
    emit('upload-success', imageFile.value);
  } catch (error) {
    emit('upload-error', error);
  } finally {
    isUploading.value = false;
  }
};

const cancelUpload = () => {
  imageFile.value = null;
  imagePreview.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
  emit('update:image', null);
};
</script>

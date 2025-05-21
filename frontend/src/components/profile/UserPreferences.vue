<template>
  <div class="space-y-4">
    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Notification Preferences</h3>
    
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Email Notifications</h4>
          <p class="text-sm text-gray-500 dark:text-gray-400">Receive email notifications about your bookings and events</p>
        </div>
        <div class="ml-4 flex-shrink-0">
          <button 
            type="button" 
            @click="togglePreference('email_notifications')"
            :class="[
              preferences.email_notifications 
                ? 'bg-primary dark:bg-primary-600' 
                : 'bg-gray-200 dark:bg-gray-700',
              'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-primary-600 focus:ring-offset-2'
            ]"
          >
            <span 
              :class="[
                preferences.email_notifications 
                  ? 'translate-x-5' 
                  : 'translate-x-0',
                'pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white dark:bg-gray-200 shadow ring-0 transition duration-200 ease-in-out'
              ]"
            >
              <span 
                :class="[
                  preferences.email_notifications 
                    ? 'opacity-0 duration-100 ease-out' 
                    : 'opacity-100 duration-200 ease-in',
                  'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity'
                ]"
              >
                <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                  <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
              <span 
                :class="[
                  preferences.email_notifications 
                    ? 'opacity-100 duration-200 ease-in' 
                    : 'opacity-0 duration-100 ease-out',
                  'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity'
                ]"
              >
                <svg class="h-3 w-3 text-primary-600" fill="currentColor" viewBox="0 0 12 12">
                  <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
                </svg>
              </span>
            </span>
          </button>
        </div>
      </div>
      
      <div class="flex items-center justify-between">
        <div>
          <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">SMS Notifications</h4>
          <p class="text-sm text-gray-500 dark:text-gray-400">Receive text messages for important updates</p>
        </div>
        <div class="ml-4 flex-shrink-0">
          <button 
            type="button" 
            @click="togglePreference('sms_notifications')"
            :class="[
              preferences.sms_notifications 
                ? 'bg-primary dark:bg-primary-600' 
                : 'bg-gray-200 dark:bg-gray-700',
              'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-primary-600 focus:ring-offset-2'
            ]"
          >
            <span 
              :class="[
                preferences.sms_notifications 
                  ? 'translate-x-5' 
                  : 'translate-x-0',
                'pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white dark:bg-gray-200 shadow ring-0 transition duration-200 ease-in-out'
              ]"
            >
              <span 
                :class="[
                  preferences.sms_notifications 
                    ? 'opacity-0 duration-100 ease-out' 
                    : 'opacity-100 duration-200 ease-in',
                  'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity'
                ]"
              >
                <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                  <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
              <span 
                :class="[
                  preferences.sms_notifications 
                    ? 'opacity-100 duration-200 ease-in' 
                    : 'opacity-0 duration-100 ease-out',
                  'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity'
                ]"
              >
                <svg class="h-3 w-3 text-primary-600" fill="currentColor" viewBox="0 0 12 12">
                  <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
                </svg>
              </span>
            </span>
          </button>
        </div>
      </div>
    </div>
    
    <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Privacy Settings</h3>
      
      <div class="mt-4 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Marketing Emails</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">Receive emails about new features and events</p>
          </div>
          <div class="ml-4 flex-shrink-0">
            <button 
              type="button" 
              @click="togglePreference('marketing_emails')"
              :class="[
                preferences.marketing_emails 
                  ? 'bg-primary dark:bg-primary-600' 
                  : 'bg-gray-200 dark:bg-gray-700',
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-primary-600 focus:ring-offset-2'
              ]"
            >
              <span 
                :class="[
                  preferences.marketing_emails 
                    ? 'translate-x-5' 
                    : 'translate-x-0',
                  'pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white dark:bg-gray-200 shadow ring-0 transition duration-200 ease-in-out'
                ]"
              >
                <span 
                  :class="[
                    preferences.marketing_emails 
                      ? 'opacity-0 duration-100 ease-out' 
                      : 'opacity-100 duration-200 ease-in',
                    'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity'
                  ]"
                >
                  <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                    <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </span>
                <span 
                  :class="[
                    preferences.marketing_emails 
                      ? 'opacity-100 duration-200 ease-in' 
                      : 'opacity-0 duration-100 ease-out',
                    'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity'
                  ]"
                >
                  <svg class="h-3 w-3 text-primary-600" fill="currentColor" viewBox="0 0 12 12">
                    <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
                  </svg>
                </span>
              </span>
            </button>
          </div>
        </div>
        
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Public Profile</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">Allow others to see your profile information</p>
          </div>
          <div class="ml-4 flex-shrink-0">
            <button 
              type="button" 
              @click="togglePreference('public_profile')"
              :class="[
                preferences.public_profile 
                  ? 'bg-primary dark:bg-primary-600' 
                  : 'bg-gray-200 dark:bg-gray-700',
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-primary-600 focus:ring-offset-2'
              ]"
            >
              <span 
                :class="[
                  preferences.public_profile 
                    ? 'translate-x-5' 
                    : 'translate-x-0',
                  'pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white dark:bg-gray-200 shadow ring-0 transition duration-200 ease-in-out'
                ]"
              >
                <span 
                  :class="[
                    preferences.public_profile 
                      ? 'opacity-0 duration-100 ease-out' 
                      : 'opacity-100 duration-200 ease-in',
                    'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity'
                  ]"
                >
                  <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                    <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </span>
                <span 
                  :class="[
                    preferences.public_profile 
                      ? 'opacity-100 duration-200 ease-in' 
                      : 'opacity-0 duration-100 ease-out',
                    'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity'
                  ]"
                >
                  <svg class="h-3 w-3 text-primary-600" fill="currentColor" viewBox="0 0 12 12">
                    <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
                  </svg>
                </span>
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="flex justify-end mt-6">
      <button 
        @click="savePreferences"
        class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors"
        :disabled="isSaving"
      >
        <span v-if="isSaving">Saving...</span>
        <span v-else>Save Preferences</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, defineProps, defineEmits, watch } from 'vue';

interface UserPreferences {
  email_notifications: boolean;
  sms_notifications: boolean;
  marketing_emails: boolean;
  public_profile: boolean;
}

const props = defineProps({
  initialPreferences: {
    type: Object as () => UserPreferences,
    default: () => ({
      email_notifications: true,
      sms_notifications: false,
      marketing_emails: true,
      public_profile: false
    })
  }
});

const emit = defineEmits(['save']);

const preferences = ref<UserPreferences>({
  email_notifications: props.initialPreferences.email_notifications,
  sms_notifications: props.initialPreferences.sms_notifications,
  marketing_emails: props.initialPreferences.marketing_emails,
  public_profile: props.initialPreferences.public_profile
});

const isSaving = ref(false);

// Watch for changes in initialPreferences
watch(() => props.initialPreferences, (newPrefs) => {
  preferences.value = { ...newPrefs };
}, { deep: true });

const togglePreference = (key: keyof UserPreferences) => {
  preferences.value[key] = !preferences.value[key];
};

const savePreferences = async () => {
  isSaving.value = true;
  
  try {
    // Emit the save event with the current preferences
    emit('save', preferences.value);
  } finally {
    isSaving.value = false;
  }
};
</script>

<template>
  <div>
    <button 
      @click="shareEvent" 
      class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
      </svg>
      Share
    </button>

    <!-- Share Dialog -->
    <div 
      v-if="showShareDialog" 
      class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center p-4" 
      @click="showShareDialog = false"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-md w-full p-6" @click.stop>
        <h3 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4">Share This Event</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <button @click="shareVia('facebook')" class="flex flex-col items-center justify-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30">
            <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M18.77,7.46H14.5v-1.9c0-0.9,0.6-1.1,1-1.1h3V0.3h-4.3c-4.3,0-5.3,2.7-5.3,4.4v2.8h-2.5v4.2h2.5v12.3h5.3V11.4h3.6L18.77,7.46z" />
            </svg>
            <span class="mt-2 text-sm">Facebook</span>
          </button>

          <button @click="shareVia('twitter')" class="flex flex-col items-center justify-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30">
            <svg class="h-8 w-8 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
            </svg>
            <span class="mt-2 text-sm">Twitter</span>
          </button>

          <button @click="shareVia('linkedin')" class="flex flex-col items-center justify-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30">
            <svg class="h-8 w-8 text-blue-700 dark:text-blue-500" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
            </svg>
            <span class="mt-2 text-sm">LinkedIn</span>
          </button>

          <button @click="shareVia('whatsapp')" class="flex flex-col items-center justify-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30">
            <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411" />
            </svg>
            <span class="mt-2 text-sm">WhatsApp</span>
          </button>
        </div>
        <div class="mt-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Link</label>
          <div class="flex">
            <input 
              ref="shareUrlInput"
              type="text"
              readonly
              :value="shareUrl"
              class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-l-md focus:outline-none focus:ring-primary dark:focus:ring-dark-primary focus:border-primary dark:focus:border-dark-primary bg-gray-100 dark:bg-gray-700 text-text-light dark:text-text-dark"
            />
            <button 
              @click="copyShareUrl"
              class="px-4 py-2 bg-primary dark:bg-dark-primary text-white rounded-r-md hover:bg-primary-dark dark:hover:bg-dark-primary-dark"
            >
              <svg v-if="!copied" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useNotificationStore } from '@/stores/notification';

interface Props {
  title: string;
  url: string;
}

const props = defineProps<Props>();
const notificationStore = useNotificationStore();

const showShareDialog = ref(false);
const copied = ref(false);
const shareUrl = ref(props.url);
const shareUrlInput = ref<HTMLInputElement | null>(null);

const shareEvent = () => {
  // Try to use the Web Share API if available
  if (navigator.share) {
    navigator.share({
      title: props.title,
      text: `Check out this event: ${props.title}`,
      url: props.url
    }).catch(() => {
      // Fallback to custom share dialog if Share API fails or is cancelled
      showShareDialog.value = true;
    });
  } else {
    // Fallback for browsers that don't support the Share API
    showShareDialog.value = true;
  }
};

const shareVia = (platform: string) => {
  const title = encodeURIComponent(props.title);
  const url = encodeURIComponent(props.url);
  let shareUrl = '';

  switch (platform) {
    case 'facebook':
      shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
      break;
    case 'twitter':
      shareUrl = `https://twitter.com/intent/tweet?text=${title}&url=${url}`;
      break;
    case 'linkedin':
      shareUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`;
      break;
    case 'whatsapp':
      shareUrl = `https://wa.me/?text=${title} ${url}`;
      break;
  }

  if (shareUrl) {
    window.open(shareUrl, '_blank');
  }

  showShareDialog.value = false;
};

const copyShareUrl = () => {
  const shareInput = document.createElement('textarea');
  shareInput.value = shareUrl.value;
  document.body.appendChild(shareInput);
  shareInput.select();
  document.execCommand('copy');
  document.body.removeChild(shareInput);
  
  copied.value = true;
  setTimeout(() => {
    copied.value = false;
  }, 2000);

  notificationStore.success('Link copied to clipboard');
};
</script>
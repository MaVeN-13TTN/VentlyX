import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

import './assets/main.css';

const app = createApp(App);

// Apply default container styles
app.config.globalProperties.$containerStyles = {
  base: 'container mx-auto px-4 sm:px-6 lg:px-8',
  section: 'py-12 sm:py-16 lg:py-20',
  inner: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'
};

app.use(createPinia());
app.use(router);

app.mount('#app');

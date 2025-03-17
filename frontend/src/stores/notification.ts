import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  message: string;
  duration?: number;
}

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref<Notification[]>([]);

  const addNotification = (notification: Omit<Notification, 'id'>) => {
    const id = Math.random().toString(36).substring(2);
    const duration = notification.duration || 5000; // Default 5 seconds
    
    notifications.value.push({
      id,
      ...notification
    });

    // Auto remove notification after duration
    setTimeout(() => {
      removeNotification(id);
    }, duration);
  };

  const removeNotification = (id: string) => {
    notifications.value = notifications.value.filter(n => n.id !== id);
  };

  const success = (message: string, duration?: number) => {
    addNotification({ type: 'success', message, duration });
  };

  const error = (message: string, duration?: number) => {
    addNotification({ type: 'error', message, duration });
  };

  const warning = (message: string, duration?: number) => {
    addNotification({ type: 'warning', message, duration });
  };

  const info = (message: string, duration?: number) => {
    addNotification({ type: 'info', message, duration });
  };

  return {
    notifications,
    addNotification,
    removeNotification,
    success,
    error,
    warning,
    info
  };
});
import { useAuthStore } from '@/stores/auth';
import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';

interface AuthMetaData {
  requiresAuth?: boolean;
  requiresGuest?: boolean;
  requiresAdmin?: boolean;
  requiresOrganizer?: boolean;
}

export const authGuard = async (
  to: RouteLocationNormalized,
  from: RouteLocationNormalized,
  next: NavigationGuardNext
) => {
  const authStore = useAuthStore();
  const meta = to.meta as AuthMetaData;

  // Handle two-factor authentication challenge
  if (authStore.twoFactorRequired && to.name !== 'two-factor-challenge') {
    return next({ name: 'two-factor-challenge' });
  }

  // Routes that require authentication
  if (meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Save the intended destination
      return next({
        name: 'login',
        query: { redirect: to.fullPath }
      });
    }

    // Check for admin routes
    if (meta.requiresAdmin && !authStore.isAdmin) {
      return next({ name: 'dashboard' });
    }

    // Check for organizer routes
    if (meta.requiresOrganizer && !authStore.isOrganizer) {
      return next({ name: 'dashboard' });
    }
  }

  // Routes that require guest access (login, register, etc.)
  if (meta.requiresGuest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' });
  }

  next();
};
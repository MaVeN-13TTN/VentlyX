import { createRouter, createWebHistory } from 'vue-router';
import { authGuard } from './guards/auth';

// Auth views
import LoginView from '@/views/auth/LoginView.vue';
import RegisterView from '@/views/auth/RegisterView.vue';
import ForgotPasswordView from '@/views/auth/ForgotPasswordView.vue';
import ResetPasswordView from '@/views/auth/ResetPasswordView.vue';
import TwoFactorChallengeView from '@/views/auth/TwoFactorChallengeView.vue';

// User views
import ProfileView from '@/views/user/ProfileView.vue';
import DashboardView from '@/views/user/DashboardView.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/HomeView.vue')
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('@/views/AboutView.vue')
    },

    // Auth routes
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { requiresGuest: true }
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: ForgotPasswordView,
      meta: { requiresGuest: true }
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: ResetPasswordView,
      meta: { requiresGuest: true }
    },
    {
      path: '/two-factor-challenge',
      name: 'two-factor-challenge',
      component: TwoFactorChallengeView,
      meta: { requiresGuest: true }
    },

    // User routes
    {
      path: '/profile',
      name: 'profile',
      component: ProfileView,
      meta: { requiresAuth: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
      meta: { requiresAuth: true }
    },
    {
      path: '/tickets',
      children: [
        {
          path: '',
          name: 'my-tickets',
          component: () => import('@/views/tickets/MyTicketsView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: ':id',
          name: 'ticket-details',
          component: () => import('@/views/tickets/TicketView.vue'),
          meta: { requiresAuth: true }
        }
      ]
    },

    // Event routes
    {
      path: '/events',
      children: [
        {
          path: '',
          name: 'events',
          component: () => import('@/views/events/EventListView.vue')
        },
        {
          path: ':id',
          name: 'event-details',
          component: () => import('@/views/events/EventDetailsView.vue')
        },
        {
          path: ':id/booking',
          name: 'event-booking',
          component: () => import('@/views/events/EventBookingView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: ':id/checkout',
          name: 'event-checkout',
          component: () => import('@/views/events/CheckoutView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'payment-success',
          name: 'payment-success',
          component: () => import('@/views/events/PaymentSuccessView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'create',
          name: 'event-create',
          component: () => import('@/views/events/EventCreateView.vue'),
          meta: { requiresAuth: true, requiresOrganizer: true }
        },
        {
          path: ':id/edit',
          name: 'event-edit',
          component: () => import('@/views/events/EventEditView.vue'),
          meta: { requiresAuth: true, requiresOrganizer: true }
        }
      ]
    },

    // Organizer routes
    {
      path: '/organizer',
      meta: { requiresAuth: true, requiresOrganizer: true },
      children: [
        {
          path: '',
          name: 'organizer-dashboard',
          component: () => import('@/views/organizer/DashboardView.vue')
        },
        {
          path: 'events',
          name: 'organizer-events',
          component: () => import('@/views/organizer/EventsView.vue')
        },
        {
          path: 'analytics',
          name: 'organizer-analytics',
          component: () => import('@/views/organizer/AnalyticsView.vue')
        },
        {
          path: 'attendees',
          name: 'organizer-attendees',
          component: () => import('@/views/organizer/AttendeesView.vue')
        }
      ]
    },

    // Admin routes
    {
      path: '/admin',
      meta: { requiresAuth: true, requiresAdmin: true },
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: () => import('@/views/admin/DashboardView.vue')
        },
        {
          path: 'users',
          name: 'admin-users',
          component: () => import('@/views/admin/UsersView.vue')
        },
        {
          path: 'events',
          name: 'admin-events',
          component: () => import('@/views/admin/EventsView.vue')
        },
        {
          path: 'settings',
          name: 'admin-settings',
          component: () => import('@/views/admin/SettingsView.vue')
        }
      ]
    },

    // Error pages
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/errors/NotFoundView.vue')
    }
  ]
});

// Register the authentication guard
router.beforeEach(authGuard);

export default router;

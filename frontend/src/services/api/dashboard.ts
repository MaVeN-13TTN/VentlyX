import apiClient from './client';
import type { ApiError } from './types';

export interface EventDate {
  month: string;
  day: string;
}

export interface Event {
  id: number;
  title: string;
  start_time: string;
  location: string;
  booking_id: number;
}

export interface Booking {
  id: number;
  event_title: string;
  booking_date: string;
  status: string;
  total_price: number;
}

export interface Notification {
  id: number;
  message: string;
  created_at: string;
  read_at: string | null;
}

export interface DashboardData {
  upcoming_events: Event[];
  recent_bookings: Booking[];
  saved_events: Event[];
  stats: {
    total_bookings: number;
    upcoming_events: number;
    total_spent: number;
  };
}

export interface BookingFilters {
  status?: string;
  dateRange?: [Date, Date];
  search?: string;
  page?: number;
  perPage?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
}

export interface BookingResponse {
  data: Booking[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

class DashboardService {
  /**
   * Get user dashboard data
   */
  async getUserDashboard(): Promise<DashboardData> {
    try {
      const response = await apiClient.get('/v1/analytics/user-dashboard');
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get user bookings with filters
   */
  async getUserBookings(filters: BookingFilters = {}): Promise<BookingResponse> {
    try {
      const params = new URLSearchParams();

      if (filters.status) params.append('status', filters.status);
      if (filters.dateRange) {
        params.append('start_date', filters.dateRange[0].toISOString());
        params.append('end_date', filters.dateRange[1].toISOString());
      }
      if (filters.search) params.append('search', filters.search);
      if (filters.page) params.append('page', filters.page.toString());
      if (filters.perPage) params.append('per_page', filters.perPage.toString());
      if (filters.sortBy) params.append('sort_by', filters.sortBy);
      if (filters.sortOrder) params.append('sort_order', filters.sortOrder);

      const response = await apiClient.get('/v1/bookings', { params });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get user notifications
   */
  async getNotifications(): Promise<Notification[]> {
    try {
      const response = await apiClient.get('/v1/notifications');
      return response.data.notifications;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Mark notification as read
   */
  async markNotificationAsRead(notificationId: number): Promise<void> {
    try {
      await apiClient.put(`/v1/notifications/${notificationId}/read`);
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Mark all notifications as read
   */
  async markAllNotificationsAsRead(): Promise<void> {
    try {
      await apiClient.put('/v1/notifications/read-all');
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Save an event
   */
  async saveEvent(eventId: number): Promise<void> {
    try {
      await apiClient.post(`/v1/events/${eventId}/save`);
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Remove a saved event
   */
  async removeSavedEvent(eventId: number): Promise<void> {
    try {
      await apiClient.delete(`/v1/events/${eventId}/save`);
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get saved events
   */
  async getSavedEvents(): Promise<Event[]> {
    try {
      const response = await apiClient.get('/v1/saved-events');
      return response.data.saved_events;
    } catch (error) {
      throw error as ApiError;
    }
  }
}

export default new DashboardService();

import apiClient from './client';
import type { ApiError } from './types';

export interface OrganizerStats {
  events_summary: {
    total_events: number;
    published_events: number;
    draft_events: number;
    upcoming_events: number;
  };
  sales_summary: {
    total_sales: number;
    total_tickets_sold: number;
    average_ticket_price: number;
  };
  popular_events: Array<{
    id: number;
    title: string;
    tickets_sold: number;
    revenue: number;
  }>;
  sales_by_day: Array<{
    date: string;
    amount: number;
    tickets: number;
  }>;
}

export interface OrganizerEvent {
  id: number;
  title: string;
  description: string;
  start_time: string;
  end_time: string;
  location: string;
  status: 'draft' | 'published' | 'cancelled';
  tickets_sold: number;
  tickets_available: number;
  revenue: number;
  image_url: string;
}

export interface Attendee {
  id: number;
  name: string;
  email: string;
  ticket_type: string;
  ticket_price: number;
  purchase_date: string;
  checked_in: boolean;
}

export interface EventFilters {
  status?: string;
  search?: string;
  page?: number;
  perPage?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
}

export interface EventResponse {
  data: OrganizerEvent[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface AttendeeFilters {
  eventId: number;
  search?: string;
  checkedIn?: boolean;
  ticketType?: string;
  page?: number;
  perPage?: number;
}

export interface AttendeeResponse {
  data: Attendee[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

class OrganizerService {
  /**
   * Get organizer dashboard stats
   */
  async getOrganizerStats(params?: Record<string, string>): Promise<OrganizerStats> {
    try {
      const response = await apiClient.get('/analytics/organizer-dashboard', { params });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get event-specific analytics
   */
  async getEventStats(eventId: number, params?: Record<string, string>): Promise<any> {
    try {
      const response = await apiClient.get(`/analytics/events/${eventId}`, { params });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Create a new event
   */
  async createEvent(eventData: FormData): Promise<OrganizerEvent> {
    try {
      const response = await apiClient.post('/organizer/events', eventData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      return response.data.event;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Update an existing event
   */
  async updateEvent(eventId: number, eventData: FormData): Promise<OrganizerEvent> {
    try {
      const response = await apiClient.post(`/organizer/events/${eventId}`, eventData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      return response.data.event;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Delete an event
   */
  async deleteEvent(eventId: number): Promise<void> {
    try {
      await apiClient.delete(`/organizer/events/${eventId}`);
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Duplicate an event
   */
  async duplicateEvent(eventId: number): Promise<OrganizerEvent> {
    try {
      const response = await apiClient.post(`/organizer/events/${eventId}/duplicate`);
      return response.data.event;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get ticket types for an event
   */
  async getEventTicketTypes(eventId: number): Promise<any[]> {
    try {
      const response = await apiClient.get(`/organizer/events/${eventId}/ticket-types`);
      return response.data.ticket_types;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Export sales data
   */
  async exportSalesData(params?: Record<string, string>): Promise<Blob> {
    try {
      const response = await apiClient.get('/organizer/export/sales', {
        params,
        responseType: 'blob'
      });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get organizer events with filters
   */
  async getEvents(filters: EventFilters = {}): Promise<EventResponse> {
    try {
      const params = new URLSearchParams();

      if (filters.status) params.append('status', filters.status);
      if (filters.search) params.append('search', filters.search);
      if (filters.page) params.append('page', filters.page.toString());
      if (filters.perPage) params.append('per_page', filters.perPage.toString());
      if (filters.sortBy) params.append('sort_by', filters.sortBy);
      if (filters.sortOrder) params.append('sort_order', filters.sortOrder);

      const response = await apiClient.get('/organizer/events', { params });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get event details
   */
  async getEvent(eventId: number): Promise<OrganizerEvent> {
    try {
      const response = await apiClient.get(`/organizer/events/${eventId}`);
      return response.data.event;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get event attendees
   */
  async getEventAttendees(filters: AttendeeFilters): Promise<AttendeeResponse> {
    try {
      const params = new URLSearchParams();

      if (filters.search) params.append('search', filters.search);
      if (filters.checkedIn !== undefined) params.append('checked_in', filters.checkedIn.toString());
      if (filters.ticketType) params.append('ticket_type', filters.ticketType);
      if (filters.page) params.append('page', filters.page.toString());
      if (filters.perPage) params.append('per_page', filters.perPage.toString());

      const response = await apiClient.get(`/organizer/events/${filters.eventId}/attendees`, { params });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Export attendees list as CSV
   */
  async exportAttendees(eventId: number): Promise<Blob> {
    try {
      const response = await apiClient.get(`/organizer/events/${eventId}/attendees/export`, {
        responseType: 'blob'
      });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Update attendee check-in status
   */
  async updateAttendeeCheckIn(eventId: number, attendeeId: number, checkedIn: boolean): Promise<void> {
    try {
      await apiClient.put(`/organizer/events/${eventId}/attendees/${attendeeId}/check-in`, {
        checked_in: checkedIn
      });
    } catch (error) {
      throw error as ApiError;
    }
  }
}

export default new OrganizerService();

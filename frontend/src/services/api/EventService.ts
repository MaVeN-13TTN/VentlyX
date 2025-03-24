import axios from 'axios';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

export interface EventFilters {
  category?: string;
  location?: string;
  date_from?: string;
  date_to?: string;
  search?: string;
  featured?: boolean;
  per_page?: number;
  page?: number;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
}

export interface EventData {
  id: number;
  title: string;
  description: string;
  image_url: string;
  location: string;
  category: string;
  start_time: string;
  end_time: string;
  min_price: number;
  max_price: number;
  tickets_available: number;
  organizer_id: number;
  featured: boolean;
  is_published: boolean;
  created_at: string;
  updated_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    next: string | null;
    prev: string | null;
  };
}

export default {
  getEvents(filters: EventFilters = {}) {
    return apiClient.get<PaginatedResponse<EventData>>('/events', { params: filters });
  },
  
  getFeaturedEvents(limit = 3) {
    return apiClient.get<PaginatedResponse<EventData>>('/events', { 
      params: { 
        featured: true,
        per_page: limit 
      } 
    });
  },
  
  getEventsByCategory(category: string, limit = 6) {
    return apiClient.get<PaginatedResponse<EventData>>('/events', { 
      params: { 
        category,
        per_page: limit 
      } 
    });
  },
  
  getUpcomingEvents(limit = 6) {
    const today = new Date().toISOString().split('T')[0];
    return apiClient.get<PaginatedResponse<EventData>>('/events', { 
      params: { 
        date_from: today,
        sort_by: 'start_time',
        sort_order: 'asc',
        per_page: limit 
      } 
    });
  },
  
  getCategories() {
    return apiClient.get<{categories: string[]}>('/events/categories');
  },
  
  getEvent(id: number) {
    return apiClient.get<EventData>(`/events/${id}`);
  }
}
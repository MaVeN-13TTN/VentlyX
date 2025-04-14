import axios from 'axios';
import type { AxiosResponse } from 'axios';

interface Event {
  id: number;
  title: string;
  description: string;
  start_time: string;
  image_url?: string;
  category?: string;
  price?: number;
  location?: string;
}

interface EventFilters {
  category?: string | null;
  dateRange?: [Date, Date] | null;
  location?: string;
  selectedCategories?: string[];
  priceRange?: [number, number];
  page?: number;
  per_page?: number;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
  search?: string;
}

interface EventResponse {
  data: Event[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
}

interface CategoriesResponse {
  categories: string[];
}

class EventService {
  private static instance: EventService;
  private baseURL: string;

  private constructor() {
    this.baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
  }

  public static getInstance(): EventService {
    if (!EventService.instance) {
      EventService.instance = new EventService();
    }
    return EventService.instance;
  }

  public async getCategories(): Promise<AxiosResponse<CategoriesResponse>> {
    return axios.get(`${this.baseURL}/events/categories`);
  }

  public async getEvents(filters?: EventFilters): Promise<AxiosResponse<EventResponse>> {
    const params = new URLSearchParams();

    if (filters?.category) {
      params.append('category', filters.category);
    }

    if (filters?.dateRange) {
      params.append('start_date', filters.dateRange[0].toISOString());
      params.append('end_date', filters.dateRange[1].toISOString());
    }

    if (filters?.location) {
      params.append('location', filters.location);
    }

    if (filters?.selectedCategories?.length) {
      params.append('categories', filters.selectedCategories.join(','));
    }

    if (filters?.priceRange) {
      params.append('min_price', filters.priceRange[0].toString());
      params.append('max_price', filters.priceRange[1].toString());
    }

    if (filters?.page) {
      params.append('page', filters.page.toString());
    }
    if (filters?.per_page) {
      params.append('per_page', filters.per_page.toString());
    }

    if (filters?.sort_by) {
      params.append('sort_by', filters.sort_by);
    }
    if (filters?.sort_order) {
      params.append('sort_order', filters.sort_order);
    }

    if (filters?.search) {
      params.append('search', filters.search);
    }

    return axios.get(`${this.baseURL}/events`, { params });
  }

  public async getEvent(id: number): Promise<AxiosResponse<Event>> {
    return axios.get(`${this.baseURL}/events/${id}`);
  }

  public async createEvent(event: Omit<Event, 'id'>): Promise<AxiosResponse<Event>> {
    return axios.post(`${this.baseURL}/events`, event);
  }

  public async updateEvent(id: number, event: Partial<Event>): Promise<AxiosResponse<Event>> {
    return axios.put(`${this.baseURL}/events/${id}`, event);
  }

  public async deleteEvent(id: number): Promise<AxiosResponse<void>> {
    return axios.delete(`${this.baseURL}/events/${id}`);
  }
}

export default EventService;
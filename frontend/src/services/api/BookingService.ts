import apiClient from './client';
import type { ApiError } from './client';

export interface TicketType {
  id: number;
  name: string;
  description: string;
  price: number;
  available_quantity: number;
  max_per_order: number;
  sales_start_date: string;
  sales_end_date: string;
  is_available: boolean;
}

export interface BookingRequest {
  event_id: number;
  ticket_type_id: number;
  quantity: number;
}

export interface Booking {
  id: number;
  user_id: number;
  event_id: number;
  ticket_type_id: number;
  quantity: number;
  total_price: number;
  status: string;
  payment_status: string;
  booking_reference: string;
  qr_code_url?: string;
  checked_in_at?: string;
  created_at: string;
  updated_at: string;
  event?: any;
  ticket_type?: TicketType;
}

class BookingService {
  private static instance: BookingService;

  private constructor() {}

  public static getInstance(): BookingService {
    if (!BookingService.instance) {
      BookingService.instance = new BookingService();
    }
    return BookingService.instance;
  }

  /**
   * Get ticket types for an event
   */
  async getTicketTypes(eventId: number): Promise<TicketType[]> {
    try {
      const response = await apiClient.get(`/events/${eventId}/ticket-types`);
      return response.data.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Check ticket availability for an event
   */
  async checkAvailability(eventId: number, ticketTypeId: number, quantity: number): Promise<boolean> {
    try {
      const response = await apiClient.get(`/events/${eventId}/ticket-types/availability`, {
        params: { ticket_type_id: ticketTypeId, quantity }
      });
      return response.data.available;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Create a booking
   */
  async createBooking(bookingData: BookingRequest): Promise<Booking> {
    try {
      const response = await apiClient.post('/bookings', bookingData);
      return response.data.booking;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get a booking by ID
   */
  async getBooking(bookingId: number): Promise<Booking> {
    try {
      const response = await apiClient.get(`/bookings/${bookingId}`);
      return response.data.booking;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get all bookings for the current user
   */
  async getUserBookings(params: { status?: string; page?: number } = {}): Promise<{ data: Booking[], meta: any }> {
    try {
      const response = await apiClient.get('/bookings', { params });
      return {
        data: response.data.data,
        meta: response.data.meta
      };
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Cancel a booking
   */
  async cancelBooking(bookingId: number): Promise<Booking> {
    try {
      const response = await apiClient.post(`/bookings/${bookingId}/cancel`);
      return response.data.booking;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Transfer a booking to another user
   */
  async transferBooking(bookingId: number, email: string): Promise<any> {
    try {
      const response = await apiClient.post(`/bookings/${bookingId}/transfer`, { email });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get QR code for a booking
   */
  async getQRCode(bookingId: number): Promise<string> {
    try {
      const response = await apiClient.get(`/bookings/${bookingId}/ticket`);
      return response.data.qr_code_url;
    } catch (error) {
      throw error as ApiError;
    }
  }
}

export default BookingService;

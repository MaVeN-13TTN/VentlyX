import apiClient from './client';
import type { ApiError } from './client';
import type { Booking } from './BookingService';

export interface Ticket {
  id: number;
  booking_id: number;
  ticket_number: string;
  qr_code_url: string;
  is_used: boolean;
  used_at?: string;
  created_at: string;
  updated_at: string;
  booking?: Booking;
}

class TicketService {
  private static instance: TicketService;

  private constructor() {}

  public static getInstance(): TicketService {
    if (!TicketService.instance) {
      TicketService.instance = new TicketService();
    }
    return TicketService.instance;
  }

  /**
   * Get tickets for a booking
   */
  async getTicketsForBooking(bookingId: number): Promise<Ticket[]> {
    try {
      const response = await apiClient.get(`/bookings/${bookingId}/tickets`);
      return response.data.tickets;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get a single ticket by ID
   */
  async getTicket(ticketId: number): Promise<Ticket> {
    try {
      const response = await apiClient.get(`/tickets/${ticketId}`);
      return response.data.ticket;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get all tickets for the current user
   */
  async getUserTickets(params: { status?: string; page?: number } = {}): Promise<{ data: Ticket[], meta: any }> {
    try {
      const response = await apiClient.get('/tickets', { params });
      return {
        data: response.data.data,
        meta: response.data.meta
      };
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Download ticket as PDF
   */
  async downloadTicket(ticketId: number): Promise<string> {
    try {
      const response = await apiClient.get(`/tickets/${ticketId}/download`, {
        responseType: 'blob'
      });

      // Create a blob URL for the PDF
      const blob = new Blob([response.data], { type: 'application/pdf' });
      return URL.createObjectURL(blob);
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Save ticket to local storage for offline access
   */
  saveTicketOffline(ticket: Ticket): boolean {
    try {
      // Validate ticket data
      if (!ticket || !ticket.booking_id || !ticket.ticket_number || !ticket.qr_code_url) {
        console.error('Invalid ticket data for offline storage');
        return false;
      }

      // Get existing tickets from localStorage
      const storedTickets = localStorage.getItem('offline_tickets');
      let tickets: Ticket[] = [];

      // Safely parse stored tickets
      if (storedTickets) {
        try {
          const parsed = JSON.parse(storedTickets);
          if (Array.isArray(parsed)) {
            tickets = parsed;
          }
        } catch (parseError) {
          console.error('Error parsing stored tickets, resetting storage:', parseError);
          // Reset storage if corrupted
          localStorage.removeItem('offline_tickets');
        }
      }

      // Check if ticket already exists (by booking_id since id might be temporary)
      const existingIndex = tickets.findIndex(t => t.booking_id === ticket.booking_id);
      if (existingIndex >= 0) {
        // Update existing ticket
        tickets[existingIndex] = ticket;
      } else {
        // Add new ticket
        tickets.push(ticket);
      }

      // Save back to localStorage
      localStorage.setItem('offline_tickets', JSON.stringify(tickets));

      // Also save individual ticket for faster access
      localStorage.setItem(`ticket_${ticket.booking_id}`, JSON.stringify(ticket));

      // Store last update timestamp
      localStorage.setItem('tickets_last_updated', new Date().toISOString());

      return true;
    } catch (error) {
      console.error('Failed to save ticket offline:', error);
      return false;
    }
  }

  /**
   * Get offline tickets from local storage
   */
  getOfflineTickets(): Ticket[] {
    try {
      const storedTickets = localStorage.getItem('offline_tickets');
      if (!storedTickets) return [];

      try {
        const parsed = JSON.parse(storedTickets);
        if (!Array.isArray(parsed)) return [];

        // Filter out any invalid tickets
        return parsed.filter(ticket =>
          ticket &&
          ticket.booking_id &&
          ticket.ticket_number &&
          ticket.qr_code_url
        );
      } catch (parseError) {
        console.error('Error parsing stored tickets:', parseError);
        return [];
      }
    } catch (error) {
      console.error('Failed to get offline tickets:', error);
      return [];
    }
  }

  /**
   * Get a specific offline ticket by booking ID
   */
  getOfflineTicketByBookingId(bookingId: number): Ticket | null {
    try {
      // Try to get the individual ticket first (faster)
      const storedTicket = localStorage.getItem(`ticket_${bookingId}`);
      if (storedTicket) {
        try {
          return JSON.parse(storedTicket);
        } catch (parseError) {
          console.error('Error parsing stored ticket:', parseError);
        }
      }

      // Fall back to searching in the full list
      const tickets = this.getOfflineTickets();
      return tickets.find(ticket => ticket.booking_id === bookingId) || null;
    } catch (error) {
      console.error(`Failed to get offline ticket for booking ${bookingId}:`, error);
      return null;
    }
  }

  /**
   * Check if the device has offline tickets available
   */
  hasOfflineTickets(): boolean {
    try {
      const tickets = this.getOfflineTickets();
      return tickets.length > 0;
    } catch (error) {
      return false;
    }
  }

  /**
   * Get the last time offline tickets were updated
   */
  getLastTicketUpdateTime(): Date | null {
    try {
      const timestamp = localStorage.getItem('tickets_last_updated');
      return timestamp ? new Date(timestamp) : null;
    } catch (error) {
      return null;
    }
  }
}

export default TicketService;

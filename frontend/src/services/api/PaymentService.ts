import apiClient from './client';
import type { ApiError } from './client';

export interface PaymentRequest {
  booking_id: number;
  payment_method: 'stripe' | 'mpesa';
  payment_token: string;
  currency: string;
}

export interface Payment {
  id: number;
  booking_id: number;
  payment_method: string;
  payment_id?: string;
  amount: number;
  status: string;
  transaction_details?: any;
  currency: string;
  transaction_id?: string;
  transaction_reference?: string;
  payment_date?: string;
  failure_reason?: string;
  created_at: string;
  updated_at: string;
}

export interface StripePaymentIntent {
  client_secret: string;
  id: string;
  amount: number;
  currency: string;
}

export interface MPesaPaymentRequest {
  booking_id: number;
  phone_number: string;
}

export interface MPesaPaymentResponse {
  checkout_request_id: string;
  merchant_request_id: string;
  response_code: string;
  response_description: string;
  customer_message: string;
}

class PaymentService {
  private static instance: PaymentService;

  private constructor() {}

  public static getInstance(): PaymentService {
    if (!PaymentService.instance) {
      PaymentService.instance = new PaymentService();
    }
    return PaymentService.instance;
  }

  /**
   * Process a payment using Stripe
   */
  async processStripePayment(paymentData: PaymentRequest): Promise<Payment> {
    try {
      const response = await apiClient.post('/payments', paymentData);
      return response.data.payment;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Create a Stripe payment intent
   */
  async createStripePaymentIntent(bookingId: number, currency: string = 'usd'): Promise<StripePaymentIntent> {
    try {
      const response = await apiClient.post('/payments/stripe/intent', { 
        booking_id: bookingId,
        currency
      });
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Initiate M-Pesa payment
   */
  async initiateMPesaPayment(paymentData: MPesaPaymentRequest): Promise<MPesaPaymentResponse> {
    try {
      const response = await apiClient.post('/payments/mpesa', paymentData);
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Check M-Pesa payment status
   */
  async checkMPesaPaymentStatus(checkoutRequestId: string): Promise<any> {
    try {
      const response = await apiClient.get(`/payments/mpesa/status/${checkoutRequestId}`);
      return response.data;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get payment by ID
   */
  async getPayment(paymentId: number): Promise<Payment> {
    try {
      const response = await apiClient.get(`/payments/${paymentId}`);
      return response.data.payment;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Get payment by booking ID
   */
  async getPaymentByBooking(bookingId: number): Promise<Payment> {
    try {
      const response = await apiClient.get(`/bookings/${bookingId}/payment`);
      return response.data.payment;
    } catch (error) {
      throw error as ApiError;
    }
  }

  /**
   * Generate receipt for a payment
   */
  async generateReceipt(paymentId: number): Promise<string> {
    try {
      const response = await apiClient.get(`/payments/${paymentId}/receipt`, {
        responseType: 'blob'
      });
      
      // Create a blob URL for the PDF
      const blob = new Blob([response.data], { type: 'application/pdf' });
      return URL.createObjectURL(blob);
    } catch (error) {
      throw error as ApiError;
    }
  }
}

export default PaymentService;

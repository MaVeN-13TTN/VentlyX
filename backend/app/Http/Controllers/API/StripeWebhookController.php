<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Exceptions\Api\PaymentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhook events.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook.secret')
            );
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
                'header' => $sigHeader
            ]);
            throw PaymentException::paymentProviderError('Stripe', 'Invalid webhook signature');
        }

        try {
            // Handle specific webhook events
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handleSuccessfulPayment($event->data->object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handleFailedPayment($event->data->object);
                    break;
                case 'charge.refunded':
                    $this->handleRefund($event->data->object);
                    break;
                default:
                    Log::info('Unhandled Stripe webhook event', ['type' => $event->type]);
            }

            return response()->json(['message' => 'Webhook handled successfully']);

        } catch (\Exception $e) {
            Log::error('Error processing Stripe webhook', [
                'event' => $event->type,
                'error' => $e->getMessage()
            ]);
            throw PaymentException::paymentProcessingError('Failed to process webhook: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful payment webhook event.
     */
    private function handleSuccessfulPayment($paymentIntent)
    {
        $payment = Payment::where('payment_id', $paymentIntent->id)->first();

        if (!$payment) {
            Log::warning('Payment not found for successful payment intent', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        if ($payment->status === 'completed') {
            Log::info('Payment already marked as completed', [
                'payment_id' => $payment->id,
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        $payment->update([
            'status' => 'completed',
            'transaction_details' => array_merge(
                $payment->transaction_details ?? [],
                ['webhook_confirmation' => [
                    'status' => $paymentIntent->status,
                    'confirmed_at' => now()->toIso8601String()
                ]]
            )
        ]);

        // Update booking status
        $payment->booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);

        Log::info('Payment confirmed via webhook', [
            'payment_id' => $payment->id,
            'booking_id' => $payment->booking_id
        ]);
    }

    /**
     * Handle failed payment webhook event.
     */
    private function handleFailedPayment($paymentIntent)
    {
        $payment = Payment::where('payment_id', $paymentIntent->id)->first();

        if (!$payment) {
            Log::warning('Payment not found for failed payment intent', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        $payment->update([
            'status' => 'failed',
            'transaction_details' => array_merge(
                $payment->transaction_details ?? [],
                ['failure_details' => [
                    'error' => $paymentIntent->last_payment_error,
                    'failed_at' => now()->toIso8601String()
                ]]
            )
        ]);

        // Update booking status
        $payment->booking->update([
            'status' => 'cancelled',
            'payment_status' => 'failed'
        ]);

        Log::error('Payment failed via webhook', [
            'payment_id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'error' => $paymentIntent->last_payment_error
        ]);
    }

    /**
     * Handle refund webhook event.
     */
    private function handleRefund($charge)
    {
        $payment = Payment::where('payment_id', $charge->payment_intent)->first();

        if (!$payment) {
            Log::warning('Payment not found for refund', [
                'payment_intent_id' => $charge->payment_intent
            ]);
            return;
        }

        if ($payment->status === 'refunded') {
            Log::info('Payment already marked as refunded', [
                'payment_id' => $payment->id,
                'payment_intent_id' => $charge->payment_intent
            ]);
            return;
        }

        $payment->update([
            'status' => 'refunded',
            'transaction_details' => array_merge(
                $payment->transaction_details ?? [],
                ['refund_confirmation' => [
                    'refund_id' => $charge->refunds->data[0]->id,
                    'amount' => $charge->refunds->data[0]->amount / 100,
                    'status' => $charge->refunds->data[0]->status,
                    'confirmed_at' => now()->toIso8601String()
                ]]
            )
        ]);

        // Update booking status
        $payment->booking->update([
            'status' => 'refunded',
            'payment_status' => 'refunded'
        ]);

        Log::info('Refund confirmed via webhook', [
            'payment_id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'refund_id' => $charge->refunds->data[0]->id
        ]);
    }
}

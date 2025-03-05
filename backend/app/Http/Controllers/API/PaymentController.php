<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Http\Requests\Payment\RefundPaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Exceptions\Api\PaymentException;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Process a payment for a booking.
     */
    public function processPayment(ProcessPaymentRequest $request)
    {
        $validated = $request->validated();
        $booking = Booking::findOrFail($validated['booking_id']);

        if ($booking->payment_status === 'paid') {
            throw PaymentException::alreadyPaid();
        }

        if (!in_array($validated['payment_method'], ['stripe', 'paypal'])) {
            throw PaymentException::invalidPaymentMethod($validated['payment_method']);
        }

        try {
            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $booking->total_price,
                'status' => 'pending',
                'currency' => $validated['currency']
            ]);

            // Process payment based on payment method
            try {
                if ($validated['payment_method'] === 'stripe') {
                    $paymentResult = $this->processStripePayment(
                        $payment,
                        $validated['payment_token']
                    );
                } else {
                    $paymentResult = $this->processPayPalPayment(
                        $payment,
                        $validated['payment_token']
                    );
                }
            } catch (CardException $e) {
                throw PaymentException::paymentFailed($e->getMessage(), [
                    'code' => $e->getStripeCode(),
                    'decline_code' => $e->getDeclineCode(),
                ]);
            } catch (InvalidRequestException $e) {
                throw PaymentException::paymentFailed($e->getMessage());
            } catch (\Exception $e) {
                throw PaymentException::paymentProcessingError($e->getMessage());
            }

            // Update payment and booking status
            $payment->update([
                'status' => 'completed',
                'payment_id' => $paymentResult['payment_id'],
                'transaction_details' => $paymentResult['details']
            ]);

            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment->load('booking'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($e instanceof PaymentException) {
                throw $e;
            }

            throw PaymentException::paymentProcessingError('An unexpected error occurred');
        }
    }

    /**
     * Process payment using Stripe.
     */
    private function processStripePayment(Payment $payment, string $paymentToken)
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($payment->amount * 100), // Convert to cents
                'currency' => $payment->currency,
                'payment_method' => $paymentToken,
                'confirm' => true,
                'metadata' => [
                    'booking_id' => $payment->booking_id,
                    'payment_id' => $payment->id
                ]
            ]);

            return [
                'payment_id' => $paymentIntent->id,
                'details' => [
                    'status' => $paymentIntent->status,
                    'charges' => $paymentIntent->charges->data,
                    'created' => $paymentIntent->created,
                ]
            ];
        } catch (\Exception $e) {
            throw PaymentException::paymentProviderError('Stripe', $e->getMessage());
        }
    }

    /**
     * Process payment using PayPal.
     */
    private function processPayPalPayment(Payment $payment, string $paymentToken)
    {
        // Initialize PayPal client
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.client_secret');
        $isSandbox = config('services.paypal.sandbox', true);

        $apiBase = $isSandbox
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        // Get OAuth token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiBase . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $clientSecret);

        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept-Language: en_US';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw PaymentException::paymentProviderError('PayPal', curl_error($ch));
        }
        $authResponse = json_decode($result);
        curl_close($ch);

        $accessToken = $authResponse->access_token;

        // Capture payment with token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiBase . '/v2/checkout/orders/' . $paymentToken . '/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $accessToken;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw PaymentException::paymentProviderError('PayPal', curl_error($ch));
        }
        $response = json_decode($result);
        curl_close($ch);

        if ($response->status === 'COMPLETED') {
            return [
                'status' => 'paid',
                'payment_id' => $response->id,
                'details' => [
                    'payer_id' => $response->payer->payer_id ?? null,
                    'payer_email' => $response->payer->email_address ?? null,
                    'transaction_id' => $response->purchase_units[0]->payments->captures[0]->id ?? null
                ]
            ];
        } else {
            return [
                'status' => 'failed',
                'payment_id' => $response->id ?? null,
                'details' => [
                    'error_message' => 'PayPal payment capture failed',
                    'payment_status' => $response->status ?? 'UNKNOWN',
                ]
            ];
        }
    }

    /**
     * Process a refund for a payment.
     */
    public function processRefund(RefundPaymentRequest $request)
    {
        $validated = $request->validated();
        $payment = Payment::findOrFail($validated['payment_id']);

        if ($payment->status !== 'completed') {
            throw PaymentException::refundNotAllowed('Payment is not in completed state');
        }

        if (isset($validated['amount']) && $validated['amount'] > $payment->amount) {
            throw PaymentException::invalidAmount($validated['amount'], $payment->amount);
        }

        try {
            DB::beginTransaction();

            // Process refund based on original payment method
            try {
                if ($payment->payment_method === 'stripe') {
                    $refundResult = $this->processStripeRefund(
                        $payment,
                        $validated['amount'] ?? null,
                        $validated['reason'] ?? null
                    );
                } else {
                    $refundResult = $this->processPayPalRefund(
                        $payment,
                        $validated['amount'] ?? null,
                        $validated['reason'] ?? null
                    );
                }
            } catch (\Exception $e) {
                throw PaymentException::paymentProviderError(
                    $payment->payment_method,
                    'Refund failed: ' . $e->getMessage()
                );
            }

            // Update payment status and details
            $payment->update([
                'status' => 'refunded',
                'transaction_details' => array_merge(
                    $payment->transaction_details ?? [],
                    ['refund' => $refundResult]
                )
            ]);

            // Update booking status
            $payment->booking->update([
                'status' => 'refunded',
                'payment_status' => 'refunded'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Refund processed successfully',
                'payment' => $payment->load('booking'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($e instanceof PaymentException) {
                throw $e;
            }

            throw PaymentException::paymentProcessingError('Error processing refund: ' . $e->getMessage());
        }
    }

    /**
     * Process refund using Stripe.
     */
    private function processStripeRefund(Payment $payment, ?float $amount = null, ?string $reason = null)
    {
        try {
            $refund = Refund::create([
                'payment_intent' => $payment->payment_id,
                'amount' => $amount ? (int) ($amount * 100) : null, // Convert to cents if specified
                'reason' => $reason ?? 'requested_by_customer'
            ]);

            return [
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100, // Convert back to decimal
                'status' => $refund->status,
                'reason' => $refund->reason,
                'created' => $refund->created
            ];
        } catch (\Exception $e) {
            throw PaymentException::paymentProviderError('Stripe', $e->getMessage());
        }
    }

    /**
     * Process refund using PayPal.
     */
    private function processPayPalRefund(Payment $payment, ?float $amount = null, ?string $reason = null)
    {
        // Initialize PayPal client
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.client_secret');
        $isSandbox = config('services.paypal.sandbox', true);
        $apiBase = $isSandbox
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        // Get transaction ID from original payment details
        $transactionId = $payment->transaction_details['transaction_id'] ??
            ($payment->transaction_details['details']['transaction_id'] ?? null);

        if (!$transactionId) {
            throw PaymentException::paymentProviderError(
                'PayPal',
                'Cannot process refund: Original transaction ID not found'
            );
        }

        // Get OAuth token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiBase . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $clientSecret);
        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept-Language: en_US';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw PaymentException::paymentProviderError('PayPal', curl_error($ch));
        }

        $authResponse = json_decode($result);
        curl_close($ch);
        $accessToken = $authResponse->access_token;

        // Prepare refund payload
        $refundPayload = [
            'amount' => [
                'value' => $amount ?? $payment->amount,
                'currency_code' => $payment->currency
            ],
            'note_to_payer' => $reason ?? 'Refund for cancelled booking'
        ];

        // Process the refund
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiBase . '/v2/payments/captures/' . $transactionId . '/refund');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($refundPayload));
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $accessToken;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw PaymentException::paymentProviderError('PayPal', curl_error($ch));
        }

        $response = json_decode($result);
        curl_close($ch);

        if (!isset($response->id)) {
            throw PaymentException::paymentProviderError(
                'PayPal',
                'Refund failed: ' . ($response->message ?? 'Unknown error')
            );
        }

        return [
            'refund_id' => $response->id,
            'amount' => $response->amount->value ?? $amount ?? $payment->amount,
            'status' => $response->status,
            'create_time' => $response->create_time ?? now()->toIso8601String()
        ];
    }
}

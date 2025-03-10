<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\MPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MPesaPaymentController extends Controller
{
    protected $mpesaService;

    public function __construct(MPesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    public function initiatePayment(Request $request)
    {
        $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'phone_number' => ['required', 'string'],
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->payment_status === 'paid') {
            return response()->json([
                'message' => 'Payment has already been completed for this booking'
            ], 400);
        }

        // Round up to nearest integer for M-Pesa (no decimal support)
        $amount = ceil($booking->total_price);
        $referenceCode = 'VNTLX-' . $booking->id;
        $description = 'Payment for ' . $booking->event->title;

        try {
            $response = $this->mpesaService->initiateSTKPush(
                $request->phone_number,
                $amount,
                $referenceCode,
                $description
            );

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                // Store the payment record
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'amount' => $booking->total_price,
                    'payment_method' => 'mpesa',
                    'status' => 'pending',
                    'transaction_id' => $response['CheckoutRequestID'],
                    'currency' => 'KES',
                    'meta_data' => json_encode([
                        'checkout_request_id' => $response['CheckoutRequestID'],
                        'merchant_request_id' => $response['MerchantRequestID'],
                    ])
                ]);

                return response()->json([
                    'message' => 'M-Pesa payment initiated successfully. Please check your phone.',
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'payment_id' => $payment->id
                ]);
            }

            return response()->json([
                'message' => 'Failed to initiate M-Pesa payment',
                'response' => $response
            ], 400);
        } catch (\Exception $e) {
            Log::error('M-Pesa payment initiation failed', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id
            ]);

            return response()->json([
                'message' => 'Failed to initiate payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkStatus(Request $request)
    {
        $request->validate([
            'checkout_request_id' => ['required', 'string']
        ]);

        try {
            $response = $this->mpesaService->checkTransactionStatus($request->checkout_request_id);

            // Find the payment record
            $payment = Payment::where('transaction_id', $request->checkout_request_id)->first();

            if (!$payment) {
                return response()->json([
                    'message' => 'Payment record not found'
                ], 404);
            }

            // Success scenario
            if (isset($response['ResultCode']) && $response['ResultCode'] == 0) {
                $this->updatePaymentSuccess($payment, $response);

                return response()->json([
                    'message' => 'Payment completed successfully',
                    'status' => 'completed',
                    'payment' => $payment->refresh()
                ]);
            }

            // Payment is still pending
            if (isset($response['errorCode']) && $response['errorCode'] == '500.001.1001') {
                return response()->json([
                    'message' => 'Payment is still being processed',
                    'status' => 'pending'
                ]);
            }

            // Handle specific errors
            if (isset($response['ResultCode'])) {
                $this->updatePaymentFailure($payment, $response);

                return response()->json([
                    'message' => 'Payment failed: ' . ($response['ResultDesc'] ?? 'Unknown error'),
                    'status' => 'failed',
                    'response' => $response
                ], 400);
            }

            return response()->json([
                'message' => 'Unknown payment status',
                'response' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('M-Pesa status check failed', [
                'error' => $e->getMessage(),
                'checkout_request_id' => $request->checkout_request_id
            ]);

            return response()->json([
                'message' => 'Failed to check payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        $payload = $request->all();

        Log::info('M-Pesa callback received', ['data' => $payload]);

        try {
            if (!isset($payload['Body']['stkCallback']['CheckoutRequestID'])) {
                return response()->json(['message' => 'Invalid callback data']);
            }

            $checkoutRequestId = $payload['Body']['stkCallback']['CheckoutRequestID'];
            $resultCode = $payload['Body']['stkCallback']['ResultCode'];

            $payment = Payment::where('transaction_id', $checkoutRequestId)->first();

            if (!$payment) {
                return response()->json(['message' => 'Payment not found']);
            }

            if ($resultCode == 0) {
                // Payment successful
                $this->updatePaymentSuccess($payment, $payload['Body']['stkCallback']);
                return response()->json(['message' => 'Payment processed successfully']);
            } else {
                // Payment failed
                $this->updatePaymentFailure($payment, $payload['Body']['stkCallback']);
                return response()->json(['message' => 'Payment failed']);
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa callback processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return response()->json(['message' => 'Error processing callback']);
        }
    }

    protected function updatePaymentSuccess($payment, $data)
    {
        try {
            DB::beginTransaction();

            // Update payment record
            $payment->update([
                'status' => 'completed',
                'payment_date' => now(),
                'meta_data' => json_encode([
                    'mpesa_receipt' => $data['MpesaReceiptNumber'] ?? null,
                    'transaction_date' => $data['TransactionDate'] ?? null,
                    'phone_number' => $data['PhoneNumber'] ?? null,
                    'raw_response' => $data
                ]),
            ]);

            // Update booking status
            $booking = $payment->booking;
            $booking->update([
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ]);

            // Generate QR code
            $booking->generateQrCode();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update payment success', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function updatePaymentFailure($payment, $data)
    {
        try {
            $resultDesc = $data['ResultDesc'] ?? 'Payment failed';

            $payment->update([
                'status' => 'failed',
                'failure_reason' => $resultDesc,
                'meta_data' => json_encode([
                    'result_code' => $data['ResultCode'] ?? null,
                    'result_desc' => $resultDesc,
                    'raw_response' => $data
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update payment failure', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

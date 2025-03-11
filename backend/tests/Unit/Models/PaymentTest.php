<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_can_be_created()
    {
        $booking = Booking::factory()->create();
        $paymentData = [
            'booking_id' => $booking->id,
            'amount' => 5000,
            'currency' => 'KES',
            'payment_method' => 'mpesa',
            'status' => 'pending',
            'transaction_id' => 'MP' . time(),
            'payment_date' => Carbon::now()
        ];

        $payment = Payment::create($paymentData);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 5000,
            'payment_method' => 'mpesa'
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
    }

    public function test_payment_belongs_to_booking()
    {
        $booking = Booking::factory()->create();
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id
        ]);

        $this->assertInstanceOf(Booking::class, $payment->booking);
        $this->assertEquals($booking->id, $payment->booking->id);
    }

    public function test_payment_can_be_marked_as_successful()
    {
        $payment = Payment::factory()->create([
            'status' => 'pending'
        ]);

        $payment->markAsSuccessful('TXN123456');

        $this->assertEquals('completed', $payment->fresh()->status);
        $this->assertEquals('TXN123456', $payment->fresh()->transaction_id);
        $this->assertNotNull($payment->fresh()->payment_date);
    }

    public function test_payment_can_be_marked_as_failed()
    {
        $payment = Payment::factory()->create([
            'status' => 'pending'
        ]);

        $payment->markAsFailed('Payment timeout');

        $this->assertEquals('failed', $payment->fresh()->status);
        $this->assertEquals('Payment timeout', $payment->fresh()->failure_reason);
    }

    public function test_payment_can_be_refunded()
    {
        $payment = Payment::factory()->create([
            'status' => 'completed',
            'amount' => 5000
        ]);

        $payment->refund('Customer requested refund');

        $this->assertEquals('refunded', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->refund_date);
        $this->assertEquals('Customer requested refund', $payment->fresh()->refund_reason);
    }

    public function test_payment_cannot_be_refunded_if_not_completed()
    {
        $payment = Payment::factory()->create([
            'status' => 'pending'
        ]);

        $this->expectException(\Exception::class);
        $payment->refund('Cannot refund pending payment');
    }

    public function test_payment_scope_completed()
    {
        // Create payments with different statuses
        $completedPayment = Payment::factory()->create(['status' => 'completed']);
        Payment::factory()->create(['status' => 'pending']);
        Payment::factory()->create(['status' => 'failed']);

        $completedPayments = Payment::completed()->get();

        $this->assertEquals(1, $completedPayments->count());
        $this->assertTrue($completedPayments->contains($completedPayment));
    }

    public function test_payment_can_generate_receipt_number()
    {
        $payment = Payment::factory()->create([
            'status' => 'completed',
            'transaction_id' => 'TXN123456'
        ]);

        $receiptNumber = $payment->generateReceiptNumber();

        $this->assertNotNull($receiptNumber);
        $this->assertStringStartsWith('RCPT-', $receiptNumber);
    }

    public function test_payment_can_calculate_processing_fee()
    {
        $payment = Payment::factory()->create([
            'amount' => 5000,
            'payment_method' => 'mpesa'
        ]);

        // Assuming 2.5% processing fee for M-Pesa
        $expectedFee = 125; // 2.5% of 5000
        $this->assertEquals($expectedFee, $payment->calculateProcessingFee());

        $payment->update(['payment_method' => 'stripe']);
        // Assuming 3.5% processing fee for Stripe
        $expectedFee = 175; // 3.5% of 5000
        $this->assertEquals($expectedFee, $payment->fresh()->calculateProcessingFee());
    }
}

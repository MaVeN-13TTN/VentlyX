<?php

namespace App\Models;

use App\Models\Traits\HasStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory, HasStatuses;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'payment_method',
        'payment_id',
        'amount',
        'status',
        'transaction_details',
        'currency',
        'transaction_id',
        'payment_date',
        'failure_reason',
        'refund_date',
        'refund_reason'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_details' => 'array',
        'payment_date' => 'datetime',
        'refund_date' => 'datetime'
    ];

    /**
     * Get the booking associated with this payment.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who made this payment through the booking.
     */
    public function user()
    {
        return $this->booking->user();
    }

    /**
     * Process a refund for this payment.
     */
    public function processRefund($amount = null, $reason = null)
    {
        // This will be implemented with Stripe/PayPal SDK
        // For now, just update the status
        $this->status = 'refunded';
        $this->transaction_details = array_merge($this->transaction_details ?? [], [
            'refund_amount' => $amount ?? $this->amount,
            'refund_reason' => $reason,
            'refunded_at' => now()->toIso8601String()
        ]);
        $this->save();

        // Update the related booking
        $this->booking->update(['status' => 'refunded', 'payment_status' => 'refunded']);

        return true;
    }

    /**
     * Get all possible statuses for this payment.
     */
    public function getAllStatuses(): array
    {
        return [
            'pending',
            'processing',
            'completed',
            'failed',
            'refunded',
            'cancelled'
        ];
    }

    /**
     * Get allowed status transitions for this payment.
     */
    public function getAllowedStatusTransitions(): array
    {
        return [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['completed', 'failed'],
            'completed' => ['refunded'],
            'failed' => ['pending', 'cancelled'],
            'refunded' => [],
            'cancelled' => []
        ];
    }

    public function markAsSuccessful(string $transactionId): void
    {
        $this->status = 'completed';
        $this->transaction_id = $transactionId;
        $this->payment_date = now();
        $this->save();

        // Update associated booking status
        $this->booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->status = 'failed';
        $this->failure_reason = $reason;
        $this->save();

        // Update associated booking status
        $this->booking->update([
            'payment_status' => 'failed'
        ]);
    }

    public function refund(string $reason): void
    {
        if ($this->status !== 'completed') {
            throw new \Exception('Only completed payments can be refunded');
        }

        $this->status = 'refunded';
        $this->refund_date = now();
        $this->refund_reason = $reason;
        $this->save();

        // Update associated booking status
        $this->booking->update([
            'status' => 'refunded',
            'payment_status' => 'refunded'
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function generateReceiptNumber(): string
    {
        return 'RCPT-' . strtoupper(uniqid()) . '-' . $this->id;
    }

    public function calculateProcessingFee(): float
    {
        $rates = [
            'mpesa' => 0.025, // 2.5%
            'stripe' => 0.035, // 3.5%
            'paypal' => 0.039  // 3.9%
        ];

        $rate = $rates[$this->payment_method] ?? 0.03; // default 3%
        return round($this->amount * $rate, 2);
    }
}

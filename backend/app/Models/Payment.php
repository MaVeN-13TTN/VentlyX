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
        'currency'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float',
        'transaction_details' => 'array',
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
}

<?php

namespace App\Models;

use App\Models\Traits\HasStatuses;
use App\Models\Traits\HasPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class Booking extends Model
{
    use HasFactory, HasStatuses, HasPaymentStatus;

    protected $fillable = [
        'user_id',
        'event_id',
        'ticket_type_id',
        'quantity',
        'total_price',
        'status',
        'payment_status',
        'qr_code_url',
        'checked_in_at',
        'checked_in_by',
        'transfer_code',
        'transfer_status',
        'transfer_initiated_at',
        'transfer_completed_at',
        'transferred_to',
        'transferred_from',
        'transfer_expires_at'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_price' => 'float',
        'checked_in_at' => 'datetime',
        'transfer_initiated_at' => 'datetime',
        'transfer_completed_at' => 'datetime',
        'transfer_expires_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Generate QR code for this booking
     */
    public function generateQrCode(): void
    {
        // Delete existing QR code if any
        if ($this->qr_code_url) {
            Storage::disk('public')->delete($this->qr_code_url);
        }

        // Generate QR code data
        $data = [
            'booking_id' => $this->id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'timestamp' => now()->timestamp
        ];

        // Generate unique filename using timestamp
        $filename = "qr_codes/{$this->id}_{$data['timestamp']}.png";

        // Generate and save QR code
        Storage::disk('public')->put(
            $filename,
            QrCode::format('png')
                ->size(300)
                ->generate(json_encode($data))
        );

        $this->update(['qr_code_url' => $filename]);
    }

    public function getAllStatuses(): array
    {
        return [
            'pending',
            'confirmed',
            'cancelled',
            'refunded',
            'expired'
        ];
    }

    public function getAllowedStatusTransitions(): array
    {
        return [
            'pending' => ['confirmed', 'cancelled', 'expired'],
            'confirmed' => ['cancelled', 'refunded'],
            'cancelled' => [],
            'refunded' => [],
            'expired' => []
        ];
    }

    public function canBeCheckedIn(): bool
    {
        return $this->status === 'confirmed' &&
            !$this->checked_in_at &&
            now()->between(
                $this->event->start_time,
                $this->event->end_time
            );
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) &&
            !$this->checked_in_at &&
            now()->lessThan($this->event->start_time);
    }

    public function markCheckedIn(): void
    {
        if ($this->canBeCheckedIn()) {
            $this->update(['checked_in_at' => now()]);
        }
    }

    public function canBeTransferred(): bool
    {
        return $this->status === 'confirmed' &&
            !$this->checked_in_at &&
            !$this->transfer_code &&
            $this->transfer_status !== 'pending';
    }

    public function checkIn(): void
    {
        if ($this->status !== 'confirmed') {
            throw new \Exception('Invalid booking status: ' . $this->status . '. Expected one of: [confirmed]');
        }

        if ($this->checked_in_at) {
            throw new \Exception('Booking has already been checked in');
        }

        if (now()->lt($this->event->start_time) || now()->gt($this->event->end_time)) {
            throw new \Exception('Check-in is only available during the event');
        }

        $this->update([
            'checked_in_at' => now(),
            // Only update checked_in_by if it's not already set
            'checked_in_by' => $this->checked_in_by ?: null
        ]);
    }

    public function calculateTotalPrice(): float
    {
        return $this->ticketType->price * $this->quantity;
    }

    public function cancel(): void
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('Booking cannot be cancelled');
        }
        $this->update([
            'status' => 'cancelled',
            'payment_status' => 'cancelled'
        ]);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function transfer(User $toUser): void
    {
        if ($this->status !== 'confirmed') {
            throw new \Exception('Only confirmed bookings can be transferred');
        }

        if ($this->checked_in_at) {
            throw new \Exception('Cannot transfer a checked-in booking');
        }

        if ($this->transfer_status === 'completed') {
            throw new \Exception('Booking has already been transferred');
        }

        $oldUserId = $this->user_id;

        $this->update([
            'user_id' => $toUser->id,
            'transfer_code' => null,
            'transfer_status' => 'completed',
            'transfer_completed_at' => now(),
            'transfer_expires_at' => null,
            'transferred_from' => $oldUserId,
            'transferred_to' => $toUser->id
        ]);

        // Generate new QR code for the new user
        $this->generateQrCode();
    }
}

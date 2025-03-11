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
        'transfer_code',
        'transfer_status',
        'transfer_initiated_at',
        'transfer_completed_at',
        'transferred_to',
        'transferred_from'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_price' => 'float',
        'checked_in_at' => 'datetime',
        'transfer_initiated_at' => 'datetime',
        'transfer_completed_at' => 'datetime'
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

    public function generateQrCode(): void
    {
        // Skip QR code generation in testing environment or if imagick is not available
        if (app()->environment('testing') || !extension_loaded('imagick')) {
            $this->update([
                'qr_code_url' => 'test-qr-code.png'
            ]);
            return;
        }

        $qrCode = QrCode::format('png')
            ->size(300)
            ->generate(json_encode([
                'booking_id' => $this->id,
                'event_id' => $this->event_id,
                'event_name' => $this->event->name,
                'ticket_type' => $this->ticketType->name,
                'quantity' => $this->quantity,
                'user_name' => $this->user->name,
                'user_id' => $this->user->id,
                'timestamp' => now()->timestamp
            ]));

        $filename = 'qrcodes/' . $this->id . '_' . time() . '.png';
        Storage::put('public/' . $filename, $qrCode);

        $this->update([
            'qr_code_url' => Storage::url($filename)
        ]);
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
            throw new \Exception('Booking must be confirmed to check in');
        }
        $this->update([
            'checked_in_at' => now()
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

        if ($this->transfer_status === 'completed') {
            throw new \Exception('Booking has already been transferred');
        }

        $this->update([
            'transfer_status' => 'completed',
            'transfer_code' => strtoupper(uniqid()),
            'transferred_at' => now(),
            'transferred_to' => $toUser->id,
            'transferred_from' => $this->user_id,
            'user_id' => $toUser->id
        ]);

        // Generate new QR code for the new user
        $this->generateQrCode();
    }
}

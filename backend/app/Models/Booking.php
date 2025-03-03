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
        'checked_in_at'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_price' => 'float',
        'checked_in_at' => 'datetime'
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
        $qrCode = QrCode::format('png')
            ->size(300)
            ->generate(json_encode([
                'booking_id' => $this->id,
                'event_id' => $this->event_id,
                'ticket_type' => $this->ticketType->name,
                'quantity' => $this->quantity,
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
}

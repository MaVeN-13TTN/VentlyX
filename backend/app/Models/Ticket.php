<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'ticket_type_id',
        'qr_code',
        'status',
        'check_in_status',
        'checked_in_at',
        'checked_in_by',
        'seat_number',
        'metadata'
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Get the booking that owns the ticket.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the ticket type that owns the ticket.
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    /**
     * Get the user who checked in the ticket.
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Check if the ticket is valid for check-in.
     */
    public function isValidForCheckIn(): bool
    {
        return $this->status === 'issued' &&
            $this->check_in_status === 'not_checked_in' &&
            $this->booking->status === 'confirmed';
    }

    /**
     * Mark the ticket as checked in.
     */
    public function checkIn(?int $checkedInBy = null): void
    {
        if (!$this->isValidForCheckIn()) {
            throw new \Exception('Ticket is not valid for check-in');
        }

        $this->update([
            'check_in_status' => 'checked_in',
            'checked_in_at' => now(),
            'checked_in_by' => $checkedInBy
        ]);
    }

    /**
     * Get tickets that have been issued.
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Get tickets that have been checked in.
     */
    public function scopeCheckedIn($query)
    {
        return $query->where('check_in_status', 'checked_in');
    }

    /**
     * Get tickets that have not been checked in.
     */
    public function scopeNotCheckedIn($query)
    {
        return $query->where('check_in_status', 'not_checked_in');
    }
}

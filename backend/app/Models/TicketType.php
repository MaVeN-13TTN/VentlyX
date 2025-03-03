<?php

namespace App\Models;

use App\Models\Traits\HasStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketType extends Model
{
    use HasFactory, HasStatuses;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity',
        'status',
        'max_per_order',
        'sales_start_date',
        'sales_end_date'
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
        'max_per_order' => 'integer',
        'sales_start_date' => 'datetime',
        'sales_end_date' => 'datetime'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getAllStatuses(): array
    {
        return [
            'draft',
            'active',
            'paused',
            'sold_out',
            'expired',
            'cancelled'
        ];
    }

    public function getAllowedStatusTransitions(): array
    {
        return [
            'draft' => ['active'],
            'active' => ['paused', 'sold_out', 'expired', 'cancelled'],
            'paused' => ['active', 'cancelled'],
            'sold_out' => ['active', 'cancelled'],
            'expired' => [],
            'cancelled' => []
        ];
    }

    public function getTicketsRemaining(): int
    {
        $soldTickets = $this->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('quantity');

        return max(0, $this->quantity - $soldTickets);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active' &&
               $this->getTicketsRemaining() > 0 &&
               now()->between(
                   $this->sales_start_date ?? $this->event->start_time,
                   $this->sales_end_date ?? $this->event->end_time
               );
    }

    public function canBePurchased(int $quantity): bool
    {
        return $this->isAvailable() &&
               $this->getTicketsRemaining() >= $quantity &&
               (!$this->max_per_order || $quantity <= $this->max_per_order);
    }

    public function isSoldOut(): bool
    {
        return $this->getTicketsRemaining() === 0;
    }

    public function hasExpired(): bool
    {
        return now()->isAfter($this->sales_end_date ?? $this->event->end_time);
    }

    public function canBeModified(): bool
    {
        return in_array($this->status, ['draft', 'active', 'paused']) &&
               !$this->bookings()
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->exists();
    }

    public function updateAvailability(): void
    {
        if ($this->status !== 'active') {
            return;
        }

        if ($this->isSoldOut()) {
            $this->transitionTo('sold_out');
        } elseif ($this->hasExpired()) {
            $this->transitionTo('expired');
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (TicketType $ticketType) {
            if (!$ticketType->status) {
                $ticketType->status = 'draft';
            }
        });

        static::saved(function (TicketType $ticketType) {
            $ticketType->updateAvailability();
        });
    }
}

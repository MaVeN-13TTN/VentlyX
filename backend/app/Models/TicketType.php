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
        'tickets_remaining',
        'status',
        'max_per_order',
        'sales_start_date',
        'sales_end_date',
        'is_available'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'tickets_remaining' => 'integer',
        'max_per_order' => 'integer',
        'sales_start_date' => 'datetime',
        'sales_end_date' => 'datetime',
        'is_available' => 'boolean'
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
        return $this->tickets_remaining;
    }

    public function updateTicketsRemaining(): void
    {
        $soldTickets = $this->bookings()
            ->where('status', 'confirmed')
            ->sum('quantity');

        $this->tickets_remaining = $this->quantity - $soldTickets;
        $this->save();

        if ($this->tickets_remaining <= 0 && $this->status === 'active') {
            $this->update(['status' => 'sold_out']);
        }
    }

    public function isAvailable(): bool
    {
        return $this->is_available &&
            $this->status === 'active' &&
            $this->tickets_remaining > 0 &&
            $this->isSalesOpen();
    }

    public function canBePurchased(int $quantity): bool
    {
        return $this->isAvailable() &&
            $this->getTicketsRemaining() >= $quantity &&
            (!$this->max_per_order || $quantity <= $this->max_per_order);
    }

    public function isSoldOut(): bool
    {
        return $this->tickets_remaining <= 0;
    }

    public function hasExpired(): bool
    {
        return now()->isAfter($this->sales_end_date ?? $this->event->end_time);
    }

    public function canBeModified(): bool
    {
        return !$this->bookings()
            ->where('status', 'confirmed')
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

    public function isSalesOpen(): bool
    {
        $now = now();
        $salesStarted = !$this->sales_start_date || $now->greaterThanOrEqualTo($this->sales_start_date);
        $salesNotEnded = !$this->sales_end_date || $now->lessThan($this->sales_end_date);

        return $salesStarted && $salesNotEnded;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (TicketType $ticketType) {
            if (!$ticketType->status) {
                $ticketType->status = 'draft';
            }
            if (!isset($ticketType->tickets_remaining)) {
                $ticketType->tickets_remaining = $ticketType->quantity;
            }
        });

        static::created(function (TicketType $ticketType) {
            $ticketType->updateTicketsRemaining();
        });

        static::saved(function (TicketType $ticketType) {
            $ticketType->updateAvailability();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

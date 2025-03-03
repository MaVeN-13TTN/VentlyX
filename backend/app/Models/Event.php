<?php

namespace App\Models;

use App\Models\Traits\HasStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory, HasStatuses;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'venue',
        'organizer_id',
        'category',
        'status',
        'featured',
        'image_url',
        'max_capacity'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'featured' => 'boolean',
        'max_capacity' => 'integer'
    ];

    /**
     * Get the organizer of the event.
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Get the ticket types for the event.
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    /**
     * Get the bookings for the event.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the total number of tickets sold for the event.
     */
    public function getTicketsSoldAttribute()
    {
        return $this->bookings()
            ->where('status', 'confirmed')
            ->sum('quantity');
    }

    /**
     * Get events that are currently published.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Get featured events.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function getAllStatuses(): array
    {
        return [
            'draft',
            'published',
            'cancelled',
            'postponed',
            'ended'
        ];
    }

    public function getAllowedStatusTransitions(): array
    {
        return [
            'draft' => ['published'],
            'published' => ['cancelled', 'postponed', 'ended'],
            'cancelled' => [],
            'postponed' => ['published', 'cancelled'],
            'ended' => []
        ];
    }

    public function getTicketsRemaining(): int
    {
        if ($this->max_capacity === null) {
            return PHP_INT_MAX;
        }

        $soldTickets = $this->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('quantity');

        return max(0, $this->max_capacity - $soldTickets);
    }

    public function hasAvailableTickets(): bool
    {
        return $this->getTicketsRemaining() > 0;
    }

    public function isPublished(): bool 
    {
        return $this->status === 'published';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isPostponed(): bool
    {
        return $this->status === 'postponed';
    }

    public function hasStarted(): bool
    {
        return now()->gte($this->start_time);
    }

    public function hasEnded(): bool
    {
        return now()->gte($this->end_time);
    }

    public function isHappening(): bool
    {
        return now()->between($this->start_time, $this->end_time);
    }

    public function canBeModified(): bool
    {
        return in_array($this->status, ['draft', 'published']) &&
               !$this->hasStarted() &&
               !$this->bookings()
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->exists();
    }

    public function canBeCancelled(): bool
    {
        return $this->status === 'published' && !$this->hasEnded();
    }

    public function canBePostponed(): bool
    {
        return $this->status === 'published' && !$this->hasStarted();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Event $event) {
            if (!$event->status) {
                $event->status = 'draft';
            }
        });

        static::updating(function (Event $event) {
            if ($event->isDirty('end_time') && $event->hasStarted()) {
                $event->status = 'ended';
            }
        });
    }
}

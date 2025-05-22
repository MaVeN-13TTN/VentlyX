<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'event_id',
        'discount_type',
        'discount_amount',
        'starts_at',
        'expires_at',
        'max_uses',
        'used_count',
        'min_ticket_count',
        'max_discount',
        'is_active'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function isValid()
    {
        $now = now();
        
        return $this->is_active &&
            ($this->starts_at === null || $this->starts_at <= $now) &&
            ($this->expires_at === null || $this->expires_at >= $now) &&
            ($this->max_uses === null || $this->used_count < $this->max_uses);
    }

    public function calculateDiscount($subtotal, $ticketCount = 1)
    {
        if (!$this->isValid() || $ticketCount < $this->min_ticket_count) {
            return 0;
        }

        $discount = 0;
        
        if ($this->discount_type === 'percentage') {
            $discount = $subtotal * ($this->discount_amount / 100);
            
            if ($this->max_discount !== null && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }
        } else {
            $discount = $this->discount_amount;
        }
        
        return $discount;
    }

    public function incrementUsage()
    {
        $this->used_count++;
        $this->save();
    }
}
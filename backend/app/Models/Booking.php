<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'event_id',
        'ticket_type_id',
        'quantity',
        'unit_price',
        'subtotal',
        'discount_amount',
        'discount_code_id',
        'total_price',
        'status',
        'payment_status',
        'booking_reference',
        'qr_code_url',
        'checked_in_at',
        'checked_in_by',
        'cancelled_at',
        'transfer_code',
        'transfer_status',
        'transfer_initiated_at',
        'transfer_expires_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'transfer_initiated_at' => 'datetime',
        'transfer_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function checkIn()
    {
        if ($this->status !== 'confirmed') {
            throw new \Exception('Only confirmed bookings can be checked in');
        }

        if ($this->checked_in_at) {
            throw new \Exception('Booking has already been checked in');
        }

        $this->checked_in_at = now();
        $this->save();

        return $this;
    }

    public function generateQrCode()
    {
        // Create QR code data
        $qrData = [
            'booking_id' => $this->id,
            'reference' => $this->booking_reference,
            'timestamp' => now()->timestamp
        ];

        // Generate QR code
        $qrCode = QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate(json_encode($qrData));

        // Save QR code to storage
        $path = 'qrcodes/' . $this->id . '.png';
        Storage::disk('public')->put($path, $qrCode);

        // Update booking with QR code URL
        $this->qr_code_url = Storage::disk('public')->url($path);
        $this->save();

        return $this->qr_code_url;
    }

    public function transfer(User $newUser)
    {
        if ($this->status !== 'confirmed') {
            throw new \Exception('Only confirmed bookings can be transferred');
        }

        if ($this->checked_in_at) {
            throw new \Exception('Cannot transfer a checked-in booking');
        }

        if ($this->transfer_status !== 'pending') {
            throw new \Exception('No pending transfer for this booking');
        }

        // Update booking with new user
        $this->user_id = $newUser->id;
        $this->transfer_status = 'completed';
        $this->transfer_code = null;
        $this->save();

        return $this;
    }
}
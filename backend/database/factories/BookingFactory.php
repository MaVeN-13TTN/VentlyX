<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $ticketType = TicketType::factory()->create();
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'user_id' => User::factory(),
            'event_id' => $ticketType->event_id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => $quantity,
            'total_price' => $ticketType->price * $quantity,
            'status' => 'pending',
            'payment_status' => 'pending',
            'qr_code' => null,
            'checked_in_at' => null,
            'transfer_status' => null,
            'transfer_code' => null,
            'transferred_at' => null,
            'transferred_to' => null,
            'transferred_from' => null
        ];
    }

    public function confirmed(): self
    {
        return $this->state([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'qr_code' => 'data:image/png;base64,' . base64_encode($this->faker->text)
        ]);
    }

    public function cancelled(): self
    {
        return $this->state([
            'status' => 'cancelled',
            'payment_status' => 'cancelled'
        ]);
    }

    public function checkedIn(): self
    {
        return $this->state([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'checked_in_at' => now(),
            'qr_code' => 'data:image/png;base64,' . base64_encode($this->faker->text)
        ]);
    }

    public function transferred(): self
    {
        $transferredTo = User::factory()->create();
        return $this->state([
            'transfer_status' => 'completed',
            'transfer_code' => strtoupper(uniqid()),
            'transferred_at' => now(),
            'transferred_to' => $transferredTo->id
        ]);
    }
}

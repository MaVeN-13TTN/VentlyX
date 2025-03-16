<?php

namespace Tests\Feature\Notifications;

use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\BookingConfirmationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingConfirmationNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Event $event;
    protected TicketType $ticketType;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $organizer = User::factory()->create();

        $this->event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'title' => 'Test Event',
            'venue' => 'Test Venue',
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(7)->addHours(3),
        ]);

        $this->ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'VIP Ticket',
            'price' => 100.00,
        ]);

        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'quantity' => 2,
            'total_price' => 200.00,
            'status' => 'confirmed',
        ]);
    }

    public function test_notification_contains_correct_booking_information()
    {
        $notification = new BookingConfirmationNotification($this->booking);

        // Test mail content
        $mail = $notification->toMail($this->user);

        $this->assertEquals('Booking Confirmation: Test Event', $mail->subject);
        $this->assertStringContainsString('Test Event', $mail->introLines[1]);
        $this->assertStringContainsString('Test Venue', $mail->introLines[3]);
        $this->assertStringContainsString('VIP Ticket', $mail->introLines[4]);
        $this->assertStringContainsString('2', $mail->introLines[5]); // Quantity
        $this->assertStringContainsString('200.00', $mail->introLines[6]); // Total price

        // Test array representation
        $array = $notification->toArray($this->user);

        $this->assertEquals($this->booking->id, $array['booking_id']);
        $this->assertEquals($this->event->id, $array['event_id']);
        $this->assertEquals('Test Event', $array['event_title']);
        $this->assertEquals('VIP Ticket', $array['ticket_type']);
        $this->assertEquals(2, $array['quantity']);
        $this->assertEquals(200.00, $array['total_price']);
        $this->assertEquals('booking_confirmation', $array['type']);
    }

    public function test_notification_uses_correct_channels()
    {
        $notification = new BookingConfirmationNotification($this->booking);

        $this->assertEquals(['mail', 'database'], $notification->via($this->user));
    }

    public function test_notification_is_sent_when_booking_is_confirmed()
    {
        Notification::fake();

        // Trigger the notification
        $this->user->notify(new BookingConfirmationNotification($this->booking));

        // Assert a notification was sent to the user
        Notification::assertSentTo(
            $this->user,
            BookingConfirmationNotification::class,
            function ($notification) {
                // Instead of accessing protected property, check the array representation
                $array = $notification->toArray($this->user);
                return $array['booking_id'] === $this->booking->id;
            }
        );
    }

    public function test_notification_implements_should_queue()
    {
        // Test that the notification implements ShouldQueue
        $this->assertContains(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            class_implements(BookingConfirmationNotification::class)
        );
    }
}

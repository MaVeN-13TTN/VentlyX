<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The booking instance.
     *
     * @var \App\Models\Booking
     */
    protected $booking;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Booking $booking
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $event = $this->booking->event;
        $ticketType = $this->booking->ticketType;

        return (new MailMessage)
            ->subject('Booking Confirmation: ' . $event->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking has been confirmed for the following event:')
            ->line('Event: ' . $event->title)
            ->line('Date: ' . $event->start_time->format('F j, Y, g:i a'))
            ->line('Venue: ' . $event->venue)
            ->line('Ticket Type: ' . $ticketType->name)
            ->line('Quantity: ' . $this->booking->quantity)
            ->line('Total Price: ' . config('app.currency', '$') . number_format($this->booking->total_price, 2))
            ->action('View Booking Details', url('/bookings/' . $this->booking->id))
            ->line('Thank you for using VentlyX!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $event = $this->booking->event;

        return [
            'booking_id' => $this->booking->id,
            'event_id' => $event->id,
            'event_title' => $event->title,
            'event_date' => $event->start_time->format('Y-m-d H:i:s'),
            'ticket_type' => $this->booking->ticketType->name,
            'quantity' => $this->booking->quantity,
            'total_price' => $this->booking->total_price,
            'message' => 'Your booking for ' . $event->title . ' has been confirmed.',
            'type' => 'booking_confirmation'
        ];
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\CheckInBookingRequest;
use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketType;
use App\Exceptions\Api\BookingException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of the user's bookings.
     */
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['event', 'ticketType'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'bookings' => $bookings
        ]);
    }

    /**
     * Create a new booking.
     */
    public function store(StoreBookingRequest $request)
    {
        $validated = $request->validated();
        $ticketType = TicketType::findOrFail($validated['ticket_type_id']);
        $event = Event::findOrFail($validated['event_id']);

        $this->validateEventNotEnded($event);
        $this->validateTicketSalesOpen($ticketType);
        $this->validateTicketAvailability($ticketType, $validated['quantity']);

        try {
            DB::beginTransaction();

            // Create booking
            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'event_id' => $validated['event_id'],
                'ticket_type_id' => $ticketType->id,
                'quantity' => $validated['quantity'],
                'total_price' => $ticketType->price * $validated['quantity'],
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            // Generate QR code
            $booking->generateQrCode();

            DB::commit();

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking->load(['event', 'ticketType'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw BookingException::serverError('Error creating booking: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Request $request, string $id)
    {
        $booking = Booking::with(['event', 'ticketType', 'payment'])
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'booking' => $booking
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(CancelBookingRequest $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        // Check if booking can be cancelled
        if ($booking->status === 'cancelled') {
            throw BookingException::cancellationNotAllowed('Booking is already cancelled');
        }

        if ($booking->checked_in_at) {
            throw BookingException::cancellationNotAllowed('Cannot cancel a checked-in booking');
        }

        if ($booking->status === 'confirmed' && Carbon::parse($booking->event->start_time)->isPast()) {
            throw BookingException::cancellationNotAllowed('Cannot cancel a booking for an event that has already started');
        }

        $booking->update([
            'status' => 'cancelled',
            'payment_status' => 'cancelled'
        ]);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking
        ]);
    }

    /**
     * Check in a booking at the event.
     */
    public function checkIn(CheckInBookingRequest $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->checked_in_at) {
            throw BookingException::alreadyCheckedIn();
        }

        if ($booking->status !== 'confirmed') {
            throw BookingException::invalidStatus(
                $booking->status,
                ['confirmed']
            );
        }

        $booking->update([
            'checked_in_at' => now()
        ]);

        return response()->json([
            'message' => 'Booking checked in successfully',
            'booking' => $booking
        ]);
    }

    /**
     * Helper method for testing: Validate that an event has not ended
     * @throws BookingException
     */
    protected function validateEventNotEnded(Event $event): void
    {
        // Check if event has ended
        if (Carbon::parse($event->end_time)->isPast()) {
            throw BookingException::eventEnded();
        }
    }

    /**
     * Helper method for testing: Validate that ticket sales are open
     * @throws BookingException
     */
    protected function validateTicketSalesOpen(TicketType $ticketType): void
    {
        // Check ticket sales period
        if ($ticketType->sales_end_date && Carbon::parse($ticketType->sales_end_date)->isPast()) {
            throw BookingException::bookingClosed();
        }
    }

    /**
     * Helper method for testing: Validate ticket availability
     * @throws BookingException
     */
    protected function validateTicketAvailability(TicketType $ticketType, int $quantity): void
    {
        // Get tickets remaining, accounting for potential null value
        $ticketsRemaining = $ticketType->getTicketsRemaining();

        // Check ticket availability
        if ($ticketsRemaining < $quantity) {
            throw BookingException::insufficientTickets(
                $ticketType->name,
                $ticketsRemaining,
                $quantity
            );
        }

        // Check max tickets per order
        if ($ticketType->max_per_order && $quantity > $ticketType->max_per_order) {
            throw BookingException::exceedsMaxPerOrder($ticketType->name, $ticketType->max_per_order);
        }
    }
}

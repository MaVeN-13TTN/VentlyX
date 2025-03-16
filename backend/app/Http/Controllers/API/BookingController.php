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
            ->with(['event', 'ticketType', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json([
            'bookings' => [
                'data' => collect($bookings->items())->map(function ($booking) {
                    return array_merge($booking->toArray(), [
                        'event' => $booking->event,
                        'ticketType' => $booking->ticketType,
                        'payment' => $booking->payment
                    ]);
                }),
                'meta' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total()
                ]
            ]
        ]);
    }

    /**
     * Create a new booking.
     */
    public function store(StoreBookingRequest $request)
    {
        $validated = $request->validated();
        $ticketType = TicketType::findOrFail($validated['ticket_type_id']);

        if ($ticketType->tickets_remaining < $validated['quantity']) {
            return response()->json([
                'message' => 'Not enough tickets available'
            ], 422);
        }

        $event = Event::findOrFail($validated['event_id']);

        if ($event->hasEnded()) {
            return response()->json([
                'message' => 'Cannot book tickets for an event that has already ended'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'event_id' => $validated['event_id'],
                'ticket_type_id' => $ticketType->id,
                'quantity' => $validated['quantity'],
                'total_price' => $ticketType->price * $validated['quantity'],
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            // Update tickets remaining
            $ticketType->decrement('tickets_remaining', $validated['quantity']);

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
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhereHas('event', function ($q) use ($request) {
                        $q->where('organizer_id', $request->user()->id);
                    });
            })
            ->firstOrFail();

        // Format the checked_in_at field to match the expected format in tests
        $bookingData = $booking->toArray();
        if ($booking->checked_in_at) {
            $bookingData['checked_in_at'] = $booking->checked_in_at->toJSON();
        }

        return response()->json([
            'booking' => array_merge($bookingData, [
                'event' => $booking->event,
                'ticketType' => $booking->ticketType,
                'payment' => $booking->payment
            ])
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(CancelBookingRequest $request, string $id)
    {
        $booking = Booking::with(['ticketType'])->findOrFail($id);

        try {
            DB::beginTransaction();

            $booking->update([
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            // Return tickets to available pool
            $booking->ticketType->increment('tickets_remaining', $booking->quantity);

            DB::commit();

            return response()->json([
                'message' => 'Booking cancelled successfully',
                'booking' => $booking->fresh()->load(['event', 'ticketType'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get QR code for a booking
     */
    public function getQrCode(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        // Check authorization
        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate booking status
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'QR code can only be generated for confirmed bookings'
            ], 422);
        }

        // Generate/regenerate QR code
        $booking->generateQrCode();

        return response()->json([
            'qr_code_url' => $booking->qr_code_url
        ]);
    }

    /**
     * Validate a booking QR code
     */
    public function validateQrCode(Request $request)
    {
        $request->validate([
            'qr_content' => 'required|string'
        ]);

        try {
            $data = json_decode(base64_decode($request->qr_content), true);

            if (!isset($data['booking_id'])) {
                return response()->json([
                    'message' => 'Invalid QR code format',
                    'is_valid' => false
                ], 400);
            }

            $booking = Booking::with(['event', 'user', 'ticketType'])
                ->where('id', $data['booking_id'])
                ->where('status', 'confirmed')
                ->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Invalid or expired booking',
                    'is_valid' => false
                ], 404);
            }

            return response()->json([
                'is_valid' => true,
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing QR code',
                'is_valid' => false
            ], 400);
        }
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

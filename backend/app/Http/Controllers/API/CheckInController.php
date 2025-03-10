<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckInController extends Controller
{
    /**
     * Get the list of attendees for an event with check-in status
     */
    public function getAttendees(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);

        // Ensure the user is authorized to access this event's data
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized to access this event'], 403);
        }

        $query = Booking::with(['user:id,name,email,phone_number', 'ticketType:id,name'])
            ->where('event_id', $eventId)
            ->where('status', 'confirmed');

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Filter by check-in status
        if ($request->has('checked_in')) {
            if ($request->checked_in === 'true' || $request->checked_in === '1') {
                $query->whereNotNull('checked_in_at');
            } else {
                $query->whereNull('checked_in_at');
            }
        }

        // Apply sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortField === 'checked_in_at') {
            // Custom sorting for checked_in_at to handle nulls properly
            $query->orderByRaw('checked_in_at IS NULL DESC')
                ->orderBy('checked_in_at', $sortOrder);
        } else {
            $query->orderBy($sortField, $sortOrder);
        }

        // Paginate results
        $attendees = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'attendees' => $attendees->items(),
            'meta' => [
                'current_page' => $attendees->currentPage(),
                'last_page' => $attendees->lastPage(),
                'per_page' => $attendees->perPage(),
                'total' => $attendees->total()
            ]
        ]);
    }

    /**
     * Check-in an attendee for the event
     */
    public function checkIn(Request $request, string $bookingId)
    {
        $booking = Booking::with('event')->findOrFail($bookingId);

        // Ensure the user is authorized to check in for this event
        if ($request->user()->id !== $booking->event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized to check in attendees for this event'], 403);
        }

        // Validate booking status
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'Cannot check in: booking is not confirmed'
            ], 400);
        }

        // Check if already checked in
        if ($booking->checked_in_at) {
            return response()->json([
                'message' => 'Attendee already checked in',
                'checked_in_at' => $booking->checked_in_at
            ]);
        }

        // Update booking with check-in time
        $booking->checked_in_at = now();
        $booking->checked_in_by = $request->user()->id;
        $booking->save();

        return response()->json([
            'message' => 'Attendee successfully checked in',
            'booking' => $booking
        ]);
    }

    /**
     * Verify a booking QR code and return booking details
     */
    public function verifyQrCode(Request $request)
    {
        $request->validate([
            'qr_data' => ['required', 'string'],
            'event_id' => ['required', 'exists:events,id']
        ]);

        try {
            // Decode QR data
            $qrData = json_decode($request->qr_data, true);

            if (!isset($qrData['booking_id'])) {
                return response()->json([
                    'message' => 'Invalid QR code data',
                    'valid' => false
                ], 400);
            }

            $booking = Booking::with(['event', 'user', 'ticketType'])
                ->where('id', $qrData['booking_id'])
                ->where('event_id', $request->event_id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Invalid booking or wrong event',
                    'valid' => false
                ], 404);
            }

            // Validate booking status
            if ($booking->status !== 'confirmed') {
                return response()->json([
                    'message' => 'Booking is not confirmed',
                    'valid' => false,
                    'status' => $booking->status
                ], 400);
            }

            // Check if already checked in
            if ($booking->checked_in_at) {
                return response()->json([
                    'message' => 'Already checked in',
                    'valid' => true,
                    'booking' => $booking,
                    'checked_in' => true,
                    'checked_in_at' => $booking->checked_in_at
                ]);
            }

            return response()->json([
                'message' => 'Valid booking',
                'valid' => true,
                'booking' => $booking,
                'checked_in' => false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing QR code: ' . $e->getMessage(),
                'valid' => false
            ], 400);
        }
    }

    /**
     * Undo/revert a check-in
     */
    public function undoCheckIn(Request $request, string $bookingId)
    {
        $booking = Booking::with('event')->findOrFail($bookingId);

        // Ensure the user is authorized
        if ($request->user()->id !== $booking->event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized to modify check-ins for this event'], 403);
        }

        // Check if not checked in
        if (!$booking->checked_in_at) {
            return response()->json([
                'message' => 'Attendee has not been checked in'
            ], 400);
        }

        // Reset check-in status
        $booking->checked_in_at = null;
        $booking->checked_in_by = null;
        $booking->save();

        return response()->json([
            'message' => 'Check-in successfully reverted',
            'booking' => $booking
        ]);
    }

    /**
     * Get check-in statistics for an event
     */
    public function getCheckInStats(string $eventId)
    {
        $event = Event::findOrFail($eventId);

        $totalConfirmed = Booking::where('event_id', $eventId)
            ->where('status', 'confirmed')
            ->sum('quantity');

        $totalCheckedIn = Booking::where('event_id', $eventId)
            ->where('status', 'confirmed')
            ->whereNotNull('checked_in_at')
            ->sum('quantity');

        // Check-in rate by hour (in the event's time zone)
        $checkInsByHour = DB::table('bookings')
            ->select(DB::raw('HOUR(checked_in_at) as hour'), DB::raw('COUNT(*) as count'))
            ->where('event_id', $eventId)
            ->whereNotNull('checked_in_at')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Check-in rate by ticket type
        $checkInsByTicketType = DB::table('bookings')
            ->join('ticket_types', 'bookings.ticket_type_id', '=', 'ticket_types.id')
            ->select(
                'ticket_types.name',
                DB::raw('COUNT(CASE WHEN bookings.checked_in_at IS NOT NULL THEN 1 END) as checked_in'),
                DB::raw('COUNT(*) as total')
            )
            ->where('bookings.event_id', $eventId)
            ->where('bookings.status', 'confirmed')
            ->groupBy('ticket_types.name')
            ->get();

        return response()->json([
            'total_attendees' => $totalConfirmed,
            'checked_in' => $totalCheckedIn,
            'check_in_rate' => $totalConfirmed > 0 ? round(($totalCheckedIn / $totalConfirmed) * 100, 2) : 0,
            'check_ins_by_hour' => $checkInsByHour,
            'check_ins_by_ticket_type' => $checkInsByTicketType,
        ]);
    }

    /**
     * Bulk check-in multiple attendees
     */
    public function bulkCheckIn(Request $request)
    {
        $request->validate([
            'booking_ids' => ['required', 'array'],
            'booking_ids.*' => ['required', 'exists:bookings,id']
        ]);

        $bookingIds = $request->booking_ids;
        $currentUserId = $request->user()->id;
        $isAdmin = $request->user()->hasRole('Admin');

        // Get all the bookings
        $bookings = Booking::with('event')
            ->whereIn('id', $bookingIds)
            ->where('status', 'confirmed')
            ->get();

        // Group bookings by event to check authorization for each event
        $bookingsByEvent = $bookings->groupBy('event_id');

        $success = [];
        $failed = [];

        foreach ($bookingsByEvent as $eventId => $eventBookings) {
            $event = $eventBookings[0]->event;

            // Check if user is authorized for this event
            if ($currentUserId === $event->organizer_id || $isAdmin) {
                foreach ($eventBookings as $booking) {
                    try {
                        // Only check-in if not already checked in
                        if (!$booking->checked_in_at) {
                            $booking->checked_in_at = now();
                            $booking->checked_in_by = $currentUserId;
                            $booking->save();
                            $success[] = $booking->id;
                        } else {
                            // Already checked in, still count as success
                            $success[] = $booking->id;
                        }
                    } catch (\Exception $e) {
                        $failed[] = [
                            'id' => $booking->id,
                            'reason' => 'Error updating record: ' . $e->getMessage()
                        ];
                    }
                }
            } else {
                // User not authorized for this event
                foreach ($eventBookings as $booking) {
                    $failed[] = [
                        'id' => $booking->id,
                        'reason' => 'Unauthorized to check in attendees for this event'
                    ];
                }
            }
        }

        return response()->json([
            'message' => 'Bulk check-in processed',
            'success_count' => count($success),
            'failed_count' => count($failed),
            'success' => $success,
            'failed' => $failed
        ]);
    }
}

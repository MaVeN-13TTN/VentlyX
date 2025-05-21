<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Notification;
use App\Models\SavedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAnalyticsController extends Controller
{
    /**
     * Get user dashboard data
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserDashboard()
    {
        $user = Auth::user();

        // Get user's bookings
        $bookings = Booking::where('user_id', $user->id)
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get upcoming events (events that the user has booked and haven't happened yet)
        $upcomingEvents = [];
        $recentBookings = [];

        foreach ($bookings as $booking) {
            if ($booking->event && $booking->event->start_time > now()) {
                $upcomingEvents[] = [
                    'id' => $booking->event->id,
                    'title' => $booking->event->title,
                    'start_time' => $booking->event->start_time,
                    'location' => $booking->event->location,
                    'booking_id' => $booking->id
                ];
            }

            // Get recent bookings
            if (count($recentBookings) < 5) {
                $recentBookings[] = [
                    'id' => $booking->id,
                    'event_title' => $booking->event ? $booking->event->title : 'Unknown Event',
                    'booking_date' => $booking->created_at,
                    'status' => $booking->status,
                    'total_price' => (float) $booking->total_price
                ];
            }
        }

        // Limit upcoming events to 3
        $upcomingEvents = array_slice($upcomingEvents, 0, 3);

        // Get saved events
        $savedEvents = SavedEvent::where('user_id', $user->id)
            ->with('event')
            ->get()
            ->map(function ($savedEvent) {
                $event = $savedEvent->event;
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start_time' => $event->start_time,
                    'location' => $event->location
                ];
            })
            ->take(5)
            ->toArray();

        // Get stats
        $stats = [
            'total_bookings' => $bookings->count(),
            'upcoming_events' => count($upcomingEvents),
            'total_spent' => $bookings->sum('total_price')
        ];

        return response()->json([
            'upcoming_events' => $upcomingEvents,
            'recent_bookings' => $recentBookings,
            'saved_events' => $savedEvents,
            'stats' => $stats
        ]);
    }
}

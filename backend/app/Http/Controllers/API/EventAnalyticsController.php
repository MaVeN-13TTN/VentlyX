<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Booking;
use App\Models\TicketType;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventAnalyticsController extends Controller
{
    /**
     * Get comprehensive stats for a specific event
     */
    public function getEventStats(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        // Check authorization - only allow organizer or admin
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized to access event analytics'], 403);
        }

        // Set date ranges for filtering if provided
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        // Base query for bookings
        $bookingsQuery = $event->bookings();
        if ($startDate) {
            $bookingsQuery->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $bookingsQuery->where('created_at', '<=', $endDate);
        }

        // Attendance statistics
        $totalBookings = $bookingsQuery->count();
        $totalConfirmedBookings = (clone $bookingsQuery)->where('status', 'confirmed')->count();
        $totalCancelledBookings = (clone $bookingsQuery)->where('status', 'cancelled')->count();

        $totalTicketsSold = (clone $bookingsQuery)
            ->where('status', 'confirmed')
            ->sum('quantity');

        $totalRevenue = (clone $bookingsQuery)
            ->where('status', 'confirmed')
            ->sum('total_price');

        // Calculate check-in rate
        $checkedInCount = (clone $bookingsQuery)
            ->where('status', 'confirmed')
            ->whereNotNull('checked_in_at')
            ->count();

        $checkInRate = $totalConfirmedBookings > 0
            ? round(($checkedInCount / $totalConfirmedBookings) * 100, 2)
            : 0;

        // Ticket type distribution
        $ticketTypeDistribution = $this->getTicketTypeDistribution($event->id, $startDate, $endDate);

        // Sales over time
        $dailySales = $this->getDailySalesStats($event->id, $startDate, $endDate);
        $hourlySales = $this->getHourlySalesStats($event->id, $startDate, $endDate);

        // Payment method analytics
        $paymentMethods = $this->getPaymentMethodStats($event->id, $startDate, $endDate);

        // Booking source analytics (where bookings are coming from)
        $bookingSources = $this->getBookingSourceStats($event->id, $startDate, $endDate);

        // Conversion rate (views to bookings)
        $viewCount = $event->view_count ?? 0;
        $conversionRate = $viewCount > 0
            ? round(($totalConfirmedBookings / $viewCount) * 100, 2)
            : 0;

        // Capacity utilization
        $totalCapacity = TicketType::where('event_id', $event->id)
            ->sum('quantity');

        $capacityUtilization = $totalCapacity > 0
            ? round(($totalTicketsSold / $totalCapacity) * 100, 2)
            : 0;

        // Customer demographics (if available)
        $demographics = $this->getDemographics($event->id);

        // Refund data
        $refundData = $this->getRefundStats($event->id, $startDate, $endDate);

        // Check-in time distribution
        $checkInTimeDistribution = $this->getCheckInTimeDistribution($event->id);

        return response()->json([
            'general' => [
                'total_bookings' => $totalBookings,
                'confirmed_bookings' => $totalConfirmedBookings,
                'cancelled_bookings' => $totalCancelledBookings,
                'total_tickets_sold' => $totalTicketsSold,
                'total_revenue' => $totalRevenue,
                'check_in_rate' => $checkInRate,
                'conversion_rate' => $conversionRate,
                'capacity_utilization' => $capacityUtilization
            ],
            'ticket_types' => $ticketTypeDistribution,
            'sales_trends' => [
                'daily' => $dailySales,
                'hourly' => $hourlySales
            ],
            'payment_methods' => $paymentMethods,
            'booking_sources' => $bookingSources,
            'demographics' => $demographics,
            'refund_data' => $refundData,
            'check_in_times' => $checkInTimeDistribution
        ]);
    }

    /**
     * Get overall stats for all events (admin only)
     */
    public function getOverallStats(Request $request)
    {
        // Set date ranges for filtering if provided
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();

        // Event metrics
        $totalEvents = Event::count();
        $publishedEvents = Event::where('status', 'published')->count();
        $upcomingEvents = Event::where('start_time', '>', now())->count();

        // Sales metrics
        $totalSales = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->sum('total_price');

        $totalTicketsSold = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->sum('quantity');

        // Popular categories
        $popularCategories = Event::select('category', DB::raw('count(*) as count'))
            ->join('bookings', 'events.id', '=', 'bookings.event_id')
            ->where('bookings.status', 'confirmed')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Popular events
        $popularEvents = Event::select('events.id', 'events.title', DB::raw('sum(bookings.quantity) as tickets_sold'))
            ->join('bookings', 'events.id', '=', 'bookings.event_id')
            ->where('bookings.status', 'confirmed')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->groupBy('events.id', 'events.title')
            ->orderBy('tickets_sold', 'desc')
            ->limit(10)
            ->get();

        // Sales trends
        $dailyRevenue = Booking::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price) as revenue')
        )
            ->where('status', 'confirmed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'total_events' => $totalEvents,
            'published_events' => $publishedEvents,
            'upcoming_events' => $upcomingEvents,
            'total_sales' => $totalSales,
            'total_tickets_sold' => $totalTicketsSold,
            'popular_categories' => $popularCategories,
            'popular_events' => $popularEvents,
            'daily_revenue' => $dailyRevenue
        ]);
    }

    /**
     * Get organizer's dashboard stats across all their events
     */
    public function getOrganizerStats(Request $request)
    {
        $organizerId = $request->user()->id;

        // Date range filtering
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();

        // Events summary
        $totalEvents = Event::where('organizer_id', $organizerId)->count();
        $publishedEvents = Event::where('organizer_id', $organizerId)
            ->where('status', 'published')
            ->count();
        $draftEvents = Event::where('organizer_id', $organizerId)
            ->where('status', 'draft')
            ->count();
        $upcomingEvents = Event::where('organizer_id', $organizerId)
            ->where('start_time', '>', now())
            ->count();

        // Sales metrics across all events
        $events = Event::where('organizer_id', $organizerId)->pluck('id');

        $totalSales = Booking::whereIn('event_id', $events)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->sum('total_price');

        $totalTicketsSold = Booking::whereIn('event_id', $events)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->sum('quantity');

        $salesByEvent = Booking::select(
            'events.id',
            'events.title',
            DB::raw('SUM(bookings.total_price) as revenue'),
            DB::raw('SUM(bookings.quantity) as tickets_sold'),
            DB::raw('COUNT(bookings.id) as booking_count')
        )
            ->join('events', 'bookings.event_id', '=', 'events.id')
            ->where('events.organizer_id', $organizerId)
            ->where('bookings.status', 'confirmed')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->groupBy('events.id', 'events.title')
            ->orderBy('revenue', 'desc')
            ->get();

        // Sales trends
        $dailySales = Booking::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price) as revenue')
        )
            ->whereIn('event_id', $events)
            ->where('status', 'confirmed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'events_summary' => [
                'total_events' => $totalEvents,
                'published_events' => $publishedEvents,
                'draft_events' => $draftEvents,
                'upcoming_events' => $upcomingEvents
            ],
            'sales_summary' => [
                'total_sales' => $totalSales,
                'total_tickets_sold' => $totalTicketsSold,
            ],
            'sales_by_event' => $salesByEvent,
            'daily_sales' => $dailySales
        ]);
    }

    /**
     * Get ticket type distribution for an event
     */
    private function getTicketTypeDistribution($eventId, $startDate = null, $endDate = null)
    {
        $query = DB::table('bookings')
            ->join('ticket_types', 'bookings.ticket_type_id', '=', 'ticket_types.id')
            ->select(
                'ticket_types.name',
                'ticket_types.price',
                DB::raw('COUNT(bookings.id) as booking_count'),
                DB::raw('SUM(bookings.quantity) as tickets_sold'),
                DB::raw('SUM(bookings.total_price) as revenue')
            )
            ->where('bookings.event_id', $eventId)
            ->where('bookings.status', 'confirmed');

        if ($startDate) {
            $query->where('bookings.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('bookings.created_at', '<=', $endDate);
        }

        return $query->groupBy('ticket_types.name', 'ticket_types.price')
            ->orderBy('tickets_sold', 'desc')
            ->get();
    }

    /**
     * Get daily sales stats for an event
     */
    private function getDailySalesStats($eventId, $startDate = null, $endDate = null)
    {
        $query = DB::table('bookings')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as booking_count'),
                DB::raw('SUM(quantity) as tickets_sold'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->where('event_id', $eventId)
            ->where('status', 'confirmed');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get hourly sales stats for an event
     */
    private function getHourlySalesStats($eventId, $startDate = null, $endDate = null)
    {
        $query = DB::table('bookings')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(id) as booking_count'),
                DB::raw('SUM(quantity) as tickets_sold'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->where('event_id', $eventId)
            ->where('status', 'confirmed');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * Get payment method stats for an event
     */
    private function getPaymentMethodStats($eventId, $startDate = null, $endDate = null)
    {
        $query = DB::table('payments')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->select(
                'payments.payment_method',
                DB::raw('COUNT(payments.id) as count'),
                DB::raw('SUM(payments.amount) as total_amount')
            )
            ->where('bookings.event_id', $eventId)
            ->where('payments.status', 'completed');

        if ($startDate) {
            $query->where('payments.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('payments.created_at', '<=', $endDate);
        }

        return $query->groupBy('payments.payment_method')
            ->get();
    }

    /**
     * Get booking source stats (referrer data)
     */
    private function getBookingSourceStats($eventId, $startDate = null, $endDate = null)
    {
        // This would require additional tracking in the bookings table
        // For now, return a placeholder
        return [
            ['source' => 'direct', 'count' => 0],
            ['source' => 'social', 'count' => 0],
            ['source' => 'email', 'count' => 0],
            ['source' => 'referral', 'count' => 0]
        ];
    }

    /**
     * Get demographic data for event attendees
     */
    private function getDemographics($eventId)
    {
        // This would require additional user data
        // For now, return a placeholder
        return [
            'gender' => [
                ['category' => 'male', 'count' => 0],
                ['category' => 'female', 'count' => 0],
                ['category' => 'other', 'count' => 0]
            ],
            'age_groups' => [
                ['category' => '18-24', 'count' => 0],
                ['category' => '25-34', 'count' => 0],
                ['category' => '35-44', 'count' => 0],
                ['category' => '45-54', 'count' => 0],
                ['category' => '55+', 'count' => 0]
            ]
        ];
    }

    /**
     * Get refund data for an event
     */
    private function getRefundStats($eventId, $startDate = null, $endDate = null)
    {
        $query = DB::table('payments')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->select(
                DB::raw('COUNT(payments.id) as count'),
                DB::raw('SUM(payments.amount) as total_amount')
            )
            ->where('bookings.event_id', $eventId)
            ->where('payments.status', 'refunded');

        if ($startDate) {
            $query->where('payments.updated_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('payments.updated_at', '<=', $endDate);
        }

        $refunds = $query->first();

        return [
            'refund_count' => $refunds->count ?? 0,
            'refund_amount' => $refunds->total_amount ?? 0
        ];
    }

    /**
     * Get check-in time distribution for event
     */
    private function getCheckInTimeDistribution($eventId)
    {
        return DB::table('bookings')
            ->select(
                DB::raw('HOUR(checked_in_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->where('event_id', $eventId)
            ->where('status', 'confirmed')
            ->whereNotNull('checked_in_at')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * Calculate check-in rate for an event
     */
    private function calculateCheckInRate($event)
    {
        $totalBookings = $event->bookings()
            ->where('status', 'confirmed')
            ->count();

        if ($totalBookings === 0) {
            return 0;
        }

        $checkedInBookings = $event->bookings()
            ->where('status', 'confirmed')
            ->whereNotNull('checked_in_at')
            ->count();

        return round(($checkedInBookings / $totalBookings) * 100, 2);
    }
}

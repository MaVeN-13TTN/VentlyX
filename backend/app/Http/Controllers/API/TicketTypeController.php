<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketType\StoreTicketTypeRequest;
use App\Http\Requests\TicketType\UpdateTicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);
        $ticketTypes = $event->ticketTypes;
        
        return response()->json([
            'ticket_types' => $ticketTypes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketTypeRequest $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);
        $validated = $request->validated();
        
        // Create the ticket type
        $ticketType = $event->ticketTypes()->create($validated);
        
        return response()->json([
            'message' => 'Ticket type created successfully',
            'ticket_type' => $ticketType
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $eventId, string $id)
    {
        $event = Event::findOrFail($eventId);
        $ticketType = TicketType::where('event_id', $eventId)
                                ->where('id', $id)
                                ->firstOrFail();
        
        return response()->json([
            'ticket_type' => $ticketType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketTypeRequest $request, string $eventId, string $id)
    {
        $ticketType = TicketType::where('event_id', $eventId)
                                ->where('id', $id)
                                ->firstOrFail();
        
        $validated = $request->validated();
        $ticketType->update($validated);
        
        return response()->json([
            'message' => 'Ticket type updated successfully',
            'ticket_type' => $ticketType
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $eventId, string $id)
    {
        $event = Event::findOrFail($eventId);
        $ticketType = TicketType::where('event_id', $eventId)
                                ->where('id', $id)
                                ->firstOrFail();
        
        // Check if user is authorized to delete ticket types
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Check if there are any bookings using this ticket type
        $bookingsCount = $ticketType->bookings()->count();
        if ($bookingsCount > 0) {
            return response()->json([
                'message' => 'Cannot delete ticket type with existing bookings',
                'bookings_count' => $bookingsCount
            ], 400);
        }
        
        $ticketType->delete();
        
        return response()->json([
            'message' => 'Ticket type deleted successfully'
        ]);
    }
    
    /**
     * Check availability of ticket types for an event.
     */
    public function checkAvailability(string $eventId)
    {
        $event = Event::findOrFail($eventId);
        $ticketTypes = $event->ticketTypes;
        
        $availability = $ticketTypes->map(function($ticketType) {
            return [
                'id' => $ticketType->id,
                'name' => $ticketType->name,
                'available' => $ticketType->is_available,
                'remaining' => $ticketType->tickets_remaining,
                'price' => $ticketType->price,
            ];
        });
        
        return response()->json([
            'availability' => $availability
        ]);
    }
}

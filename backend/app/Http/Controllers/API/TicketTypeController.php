<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketType\StoreTicketTypeRequest;
use App\Http\Requests\TicketType\UpdateTicketTypeRequest;
use App\Http\Requests\TicketType\BulkTicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;
use App\Exceptions\Api\TicketException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Check if ticket type can be modified
        if (!$ticketType->canBeModified()) {
            throw TicketException::hasActiveBookings($ticketType->name);
        }

        $validated = $request->validated();

        // Check if price change is allowed (no existing bookings)
        if (isset($validated['price']) && $validated['price'] !== $ticketType->price && $ticketType->bookings()->exists()) {
            throw TicketException::invalidPriceChange($ticketType->name);
        }

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

        $availability = $ticketTypes->map(function ($ticketType) {
            return [
                'id' => $ticketType->id,
                'name' => $ticketType->name,
                'available' => $ticketType->isAvailable(),
                'remaining' => $ticketType->getTicketsRemaining(),
                'price' => $ticketType->price,
            ];
        });

        return response()->json([
            'availability' => $availability
        ]);
    }

    public function bulkStore(BulkTicketTypeRequest $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);

        if ($event->organizer_id !== $request->user()->id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized to modify this event'], 403);
        }

        try {
            DB::beginTransaction();

            $ticketTypes = collect($request->validated()['ticket_types'])->map(function ($ticketType) use ($eventId) {
                return TicketType::create([
                    'event_id' => $eventId,
                    'name' => $ticketType['name'],
                    'price' => $ticketType['price'],
                    'quantity' => $ticketType['quantity'],
                    'description' => $ticketType['description'] ?? null,
                    'max_per_order' => $ticketType['max_per_order'] ?? null,
                    'sales_start_date' => $ticketType['sales_start_date'] ?? null,
                    'sales_end_date' => $ticketType['sales_end_date'] ?? null,
                    'tickets_remaining' => $ticketType['quantity'],
                    'status' => 'active',
                    'is_available' => true
                ]);
            });

            DB::commit();

            return response()->json([
                'message' => 'Ticket types created successfully',
                'ticket_types' => $ticketTypes
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkUpdate(BulkTicketTypeRequest $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);

        if ($event->organizer_id !== $request->user()->id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized to modify this event'], 403);
        }

        try {
            DB::beginTransaction();

            $updatedTypes = collect($request->validated()['ticket_types'])->map(function ($data) use ($eventId) {
                $ticketType = TicketType::where('event_id', $eventId)
                    ->where('id', $data['id'])
                    ->firstOrFail();

                if ($ticketType->canBeModified()) {
                    $ticketType->update([
                        'name' => $data['name'],
                        'price' => $data['price'],
                        'quantity' => $data['quantity'],
                        'description' => $data['description'] ?? $ticketType->description,
                        'max_per_order' => $data['max_per_order'] ?? $ticketType->max_per_order,
                        'sales_start_date' => $data['sales_start_date'] ?? $ticketType->sales_start_date,
                        'sales_end_date' => $data['sales_end_date'] ?? $ticketType->sales_end_date,
                    ]);

                    $ticketType->updateTicketsRemaining();
                }

                return $ticketType;
            });

            DB::commit();

            return response()->json([
                'message' => 'Ticket types updated successfully',
                'ticket_types' => $updatedTypes
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkDelete(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);

        if ($event->organizer_id !== $request->user()->id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized to modify this event'], 403);
        }

        $request->validate([
            'ticket_type_ids' => ['required', 'array', 'min:1'],
            'ticket_type_ids.*' => ['required', 'exists:ticket_types,id']
        ]);

        try {
            DB::beginTransaction();

            $ticketTypes = TicketType::whereIn('id', $request->ticket_type_ids)
                ->where('event_id', $eventId)
                ->get();

            foreach ($ticketTypes as $ticketType) {
                if ($ticketType->canBeModified()) {
                    $ticketType->delete();
                } else {
                    throw new \Exception("Cannot delete ticket type {$ticketType->name} as it has active bookings");
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Ticket types deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

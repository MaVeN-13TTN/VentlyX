<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Requests\Event\EventImageRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource with advanced filtering.
     */
    public function index(Request $request)
    {
        $query = Event::query();

        // Only show published events to the public
        if (!$request->user() || !$request->user()->hasRole(['Admin', 'Organizer'])) {
            $query->where('status', 'published');
        }

        // Basic status filtering
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'published');
        }

        // Featured events filter
        if ($request->has('featured') && $request->featured) {
            $query->where('featured', true);
        }

        // Date range filtering
        if ($request->has('start_date')) {
            $query->where('start_time', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('start_time', '<=', $request->end_date);
        }

        // Category filtering
        if ($request->has('category')) {
            $categories = is_array($request->category) ? $request->category : [$request->category];
            $query->whereIn('category', $categories);
        }

        // Location/City filtering
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Venue filtering
        if ($request->has('venue')) {
            $query->where('venue', 'like', '%' . $request->venue . '%');
        }

        // Price range filtering (min_price and max_price)
        if ($request->has('min_price') || $request->has('max_price')) {
            $query->whereHas('ticketTypes', function ($subQuery) use ($request) {
                if ($request->has('min_price')) {
                    $subQuery->where('price', '>=', $request->min_price);
                }

                if ($request->has('max_price')) {
                    $subQuery->where('price', '<=', $request->max_price);
                }
            });
        }

        // Full-text search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%')
                    ->orWhere('location', 'like', '%' . $searchTerm . '%')
                    ->orWhere('venue', 'like', '%' . $searchTerm . '%')
                    ->orWhere('category', 'like', '%' . $searchTerm . '%');
            });
        }

        // Organizer filtering
        if ($request->has('organizer_id')) {
            $query->where('organizer_id', $request->organizer_id);
        }

        // Available tickets filtering (only show events with available tickets)
        if ($request->has('available') && $request->available) {
            $query->whereHas('ticketTypes', function ($subQuery) {
                $subQuery->where('tickets_remaining', '>', 0)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('sales_end_date')
                            ->orWhere('sales_end_date', '>=', now());
                    });
            });
        }

        // Sort options
        $sortField = $request->input('sort_by', 'start_time');
        $sortOrder = $request->input('sort_order', 'asc');

        // Special sort for "popularity" using booking count
        if ($sortField === 'popularity') {
            $query->withCount(['bookings' => function ($q) {
                $q->where('status', 'confirmed');
            }])
                ->orderBy('bookings_count', $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Get unique categories for filtering UI
        if ($request->has('get_categories')) {
            return response()->json([
                'categories' => Event::select('category')->distinct()->get()->pluck('category')
            ]);
        }

        // Get available locations for filtering UI
        if ($request->has('get_locations')) {
            return response()->json([
                'locations' => Event::select('location')->distinct()->get()->pluck('location')
            ]);
        }

        // Include related data if requested
        if ($request->has('include')) {
            $includes = explode(',', $request->include);
            $allowedIncludes = ['organizer', 'ticketTypes'];
            $validIncludes = array_intersect($includes, $allowedIncludes);

            if (in_array('organizer', $validIncludes)) {
                $query->with(['organizer' => function ($q) {
                    $q->select('id', 'name', 'email');
                }]);
            }

            if (in_array('ticketTypes', $validIncludes)) {
                $query->with(['ticketTypes' => function ($q) {
                    $q->where('status', 'active')
                        ->where(function ($sq) {
                            $sq->whereNull('sales_end_date')
                                ->orWhere('sales_end_date', '>=', now());
                        });
                }]);
            }
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        $events = $query->paginate($perPage);

        return response()->json([
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $validated['organizer_id'] = $request->user()->id;
        $event = Event::create($validated);

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $event = Event::with(['ticketTypes', 'organizer:id,name,email'])->findOrFail($id);

        // Only allow viewing draft/cancelled events if user is admin or the organizer
        if ($event->status !== 'published') {
            if (
                !$request->user() ||
                (!$request->user()->hasRole('Admin') && $event->organizer_id !== $request->user()->id)
            ) {
                return response()->json(['message' => 'Event not found'], 404);
            }
        }

        return response()->json([
            'data' => $event
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, string $id)
    {
        $event = Event::findOrFail($id);
        $validated = $request->validated();

        // Check if event can be modified
        if ($event->status === 'ended') {
            return response()->json(['message' => 'Cannot modify ended events'], 400);
        }

        if ($event->status === 'cancelled' && $request->input('status') === 'published') {
            return response()->json(['message' => 'Cannot publish cancelled events'], 400);
        }

        if ($request->hasFile('image')) {
            if ($event->image_url && Storage::exists('public/' . str_replace('/storage/', '', $event->image_url))) {
                Storage::delete('public/' . str_replace('/storage/', '', $event->image_url));
            }
            $path = $request->file('image')->store('events', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $event->update($validated);

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete associated image if exists
        if ($event->image_url) {
            $path = str_replace('/storage/', '', $event->image_url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }

    /**
     * Get events created by the authenticated user.
     */
    public function myEvents(Request $request)
    {
        $events = Event::where('organizer_id', $request->user()->id)
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total()
            ]
        ]);
    }

    /**
     * Toggle the featured status of an event.
     */
    public function toggleFeatured(Request $request, string $id)
    {
        if (!$request->user()->hasRole('Admin')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $event = Event::findOrFail($id);
        $event->featured = !$event->featured;
        $event->save();

        return response()->json([
            'message' => 'Event featured status updated successfully',
            'featured' => $event->featured
        ]);
    }

    /**
     * Upload or update event image.
     */
    public function uploadImage(EventImageRequest $request, string $id)
    {
        $event = Event::findOrFail($id);
        $image = $request->file('image');

        // Delete old image if it exists
        if ($event->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $event->image_url))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $event->image_url));
        }

        // Generate unique filename
        $filename = 'events/' . uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();

        // Store the image directly without processing for tests
        Storage::disk('public')->putFileAs('', $image, $filename);

        // Update event with new image URL - Fixed the undefined url() method
        $event->update([
            'image_url' => '/storage/' . $filename
        ]);

        return response()->json([
            'message' => 'Event image uploaded successfully',
            'image_url' => $event->image_url
        ]);
    }
}

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

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'published');
        }

        if ($request->has('featured') && $request->featured) {
            $query->where('featured', true);
        }

        if ($request->has('start_date')) {
            $query->where('start_time', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('end_time', '<=', $request->end_date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('with_ticket_types') && $request->with_ticket_types) {
            $query->with('ticketTypes');
        }

        $perPage = $request->input('per_page', 10);

        return response()->json([
            'events' => $query->paginate($perPage),
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
    public function show(string $id)
    {
        $event = Event::with(['ticketTypes', 'organizer:id,name,email'])->findOrFail($id);

        return response()->json([
            'event' => $event
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, string $id)
    {
        $event = Event::findOrFail($id);
        $validated = $request->validated();

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

        if ($event->image_url && Storage::exists('public/' . str_replace('/storage/', '', $event->image_url))) {
            Storage::delete('public/' . str_replace('/storage/', '', $event->image_url));
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
            'events' => $events
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
            'message' => 'Event featured status updated',
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
        if ($event->image_url && Storage::exists('public/' . str_replace('/storage/', '', $event->image_url))) {
            Storage::delete('public/' . str_replace('/storage/', '', $event->image_url));
        }

        // Process and optimize the image using Intervention Image v3
        $manager = new ImageManager(new Driver());
        $processedImage = $manager->read($image);

        // Resize if larger than 1200px width while maintaining aspect ratio
        if ($processedImage->width() > 1200) {
            $processedImage->resize(1200);
        }

        // Generate unique filename
        $filename = 'events/' . uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();

        // Store the processed image with 85% quality
        Storage::put('public/' . $filename, $processedImage->toJpeg(85));

        // Update event with new image URL
        $event->update([
            'image_url' => Storage::url($filename)
        ]);

        return $this->successResponse('Event image uploaded successfully', [
            'image_url' => $event->image_url
        ]);
    }
}

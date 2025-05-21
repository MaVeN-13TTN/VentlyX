<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SavedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedEventController extends Controller
{
    /**
     * Save an event for the authenticated user
     *
     * @param  int  $eventId
     * @return \Illuminate\Http\Response
     */
    public function saveEvent($eventId)
    {
        $user = Auth::user();
        $event = Event::findOrFail($eventId);
        
        // Check if the event is already saved
        $savedEvent = SavedEvent::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();
            
        if ($savedEvent) {
            return response()->json([
                'message' => 'Event already saved'
            ]);
        }
        
        // Save the event
        SavedEvent::create([
            'user_id' => $user->id,
            'event_id' => $event->id
        ]);
        
        return response()->json([
            'message' => 'Event saved successfully'
        ]);
    }
    
    /**
     * Remove a saved event for the authenticated user
     *
     * @param  int  $eventId
     * @return \Illuminate\Http\Response
     */
    public function removeSavedEvent($eventId)
    {
        $user = Auth::user();
        
        $savedEvent = SavedEvent::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->first();
            
        if (!$savedEvent) {
            return response()->json([
                'message' => 'Event not found in saved events'
            ], 404);
        }
        
        $savedEvent->delete();
        
        return response()->json([
            'message' => 'Event removed from saved events'
        ]);
    }
    
    /**
     * Get all saved events for the authenticated user
     *
     * @return \Illuminate\Http\Response
     */
    public function getSavedEvents()
    {
        $user = Auth::user();
        
        $savedEvents = SavedEvent::where('user_id', $user->id)
            ->with('event')
            ->get()
            ->map(function ($savedEvent) {
                return $savedEvent->event;
            });
            
        return response()->json([
            'saved_events' => $savedEvents
        ]);
    }
}

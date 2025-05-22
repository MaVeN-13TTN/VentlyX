<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\OfflineService;
use Illuminate\Http\Request;

class OfflineController extends Controller
{
    protected $offlineService;
    
    public function __construct(OfflineService $offlineService)
    {
        $this->offlineService = $offlineService;
    }
    
    /**
     * Generate offline data package for an event
     */
    public function generateEventPackage(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);
        
        // Check if user is authorized to access this event's data
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized to access this event'], 403);
        }
        
        $package = $this->offlineService->generateOfflinePackage((int)$eventId);
        
        return response()->json([
            'message' => 'Offline package generated successfully',
            'package' => $package
        ]);
    }
    
    /**
     * Sync offline check-ins back to the server
     */
    public function syncCheckIns(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);
        
        // Check if user is authorized to access this event's data
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized to access this event'], 403);
        }
        
        $request->validate([
            'check_ins' => ['required', 'array'],
            'check_ins.*.booking_id' => ['required', 'integer'],
            'check_ins.*.checked_in_at' => ['required', 'string'],
            'check_ins.*.checked_in_by' => ['required', 'integer'],
            'sync_token' => ['required', 'string']
        ]);
        
        $result = $this->offlineService->syncOfflineCheckIns(
            (int)$eventId,
            $request->check_ins,
            $request->sync_token
        );
        
        return response()->json($result);
    }
    
    /**
     * Get offline data for a user's tickets
     */
    public function getUserTickets(Request $request)
    {
        $userId = $request->user()->id;
        
        $ticketsData = $this->offlineService->getUserTicketsOfflineData($userId);
        
        return response()->json([
            'message' => 'User tickets offline data generated successfully',
            'data' => $ticketsData
        ]);
    }
}
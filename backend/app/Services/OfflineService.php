<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Booking;
use App\Models\TicketType;
use Illuminate\Support\Facades\Cache;

class OfflineService
{
    /**
     * Generate offline data package for an event
     *
     * @param int $eventId Event ID
     * @return array
     */
    public function generateOfflinePackage(int $eventId): array
    {
        $event = Event::with(['ticketTypes', 'organizer'])->findOrFail($eventId);
        
        // Get all confirmed bookings for the event
        $bookings = Booking::with(['user', 'ticketType'])
            ->where('event_id', $eventId)
            ->where('status', 'confirmed')
            ->get();
            
        // Format data for offline use
        $offlineData = [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'location' => $event->location,
                'organizer' => [
                    'id' => $event->organizer->id,
                    'name' => $event->organizer->name
                ]
            ],
            'ticket_types' => $event->ticketTypes->map(function($ticketType) {
                return [
                    'id' => $ticketType->id,
                    'name' => $ticketType->name,
                    'price' => $ticketType->price
                ];
            }),
            'attendees' => $bookings->map(function($booking) {
                return [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'user_name' => $booking->user->name,
                    'user_email' => $booking->user->email,
                    'ticket_type' => $booking->ticketType->name,
                    'quantity' => $booking->quantity,
                    'checked_in' => $booking->checked_in_at ? true : false,
                    'checked_in_at' => $booking->checked_in_at ? $booking->checked_in_at->toIso8601String() : null,
                    'qr_data' => base64_encode(json_encode([
                        'booking_id' => $booking->id,
                        'reference' => $booking->booking_reference
                    ]))
                ];
            })
        ];
        
        // Generate a unique sync token
        $syncToken = md5($eventId . time());
        
        // Store the package in cache for later sync
        Cache::put("offline_package_{$eventId}_{$syncToken}", $offlineData, now()->addDays(7));
        
        return [
            'data' => $offlineData,
            'sync_token' => $syncToken,
            'generated_at' => now()->toIso8601String()
        ];
    }
    
    /**
     * Sync offline check-ins back to the server
     *
     * @param int $eventId Event ID
     * @param array $checkIns Array of check-ins performed offline
     * @param string $syncToken Sync token from the offline package
     * @return array
     */
    public function syncOfflineCheckIns(int $eventId, array $checkIns, string $syncToken): array
    {
        // Verify the sync token
        $offlinePackage = Cache::get("offline_package_{$eventId}_{$syncToken}");
        
        if (!$offlinePackage) {
            return [
                'success' => false,
                'message' => 'Invalid or expired sync token',
                'synced' => 0,
                'failed' => count($checkIns)
            ];
        }
        
        $synced = 0;
        $failed = 0;
        
        foreach ($checkIns as $checkIn) {
            try {
                $booking = Booking::find($checkIn['booking_id']);
                
                if ($booking && $booking->event_id == $eventId && !$booking->checked_in_at) {
                    $booking->checked_in_at = $checkIn['checked_in_at'];
                    $booking->checked_in_by = $checkIn['checked_in_by'];
                    $booking->save();
                    $synced++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Synced {$synced} check-ins, {$failed} failed",
            'synced' => $synced,
            'failed' => $failed
        ];
    }
    
    /**
     * Get offline data for a user's tickets
     *
     * @param int $userId User ID
     * @return array
     */
    public function getUserTicketsOfflineData(int $userId): array
    {
        $bookings = Booking::with(['event', 'ticketType'])
            ->where('user_id', $userId)
            ->where('status', 'confirmed')
            ->get();
            
        $tickets = $bookings->map(function($booking) {
            return [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'event' => [
                    'id' => $booking->event->id,
                    'title' => $booking->event->title,
                    'start_time' => $booking->event->start_time,
                    'end_time' => $booking->event->end_time,
                    'location' => $booking->event->location,
                    'image_url' => $booking->event->image_url
                ],
                'ticket_type' => [
                    'name' => $booking->ticketType->name,
                    'price' => $booking->ticketType->price
                ],
                'quantity' => $booking->quantity,
                'total_price' => $booking->total_price,
                'qr_code_data' => base64_encode(json_encode([
                    'booking_id' => $booking->id,
                    'reference' => $booking->booking_reference
                ])),
                'checked_in' => $booking->checked_in_at ? true : false
            ];
        });
        
        return [
            'tickets' => $tickets,
            'generated_at' => now()->toIso8601String(),
            'expires_at' => now()->addDays(7)->toIso8601String()
        ];
    }
}
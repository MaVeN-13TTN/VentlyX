<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get the user's notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $notifications = $request->user()
            ->notifications()
            ->paginate($perPage);

        return response()->json([
            'notifications' => $notifications->items(),
            'unread_count' => $request->user()->unreadNotifications->count(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total()
            ]
        ]);
    }

    /**
     * Get the user's unread notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unread(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $notifications = $request->user()
            ->unreadNotifications()
            ->paginate($perPage);

        return response()->json([
            'notifications' => $notifications->items(),
            'unread_count' => $request->user()->unreadNotifications->count(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total()
            ]
        ]);
    }

    /**
     * Mark a notification as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $notification->markAsRead();

        return $this->successResponse('Notification marked as read');
    }

    /**
     * Mark all notifications as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->successResponse('All notifications marked as read');
    }

    /**
     * Remove the specified notification from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        // Special case for 'all' - delegate to destroyAll method
        if ($id === 'all') {
            return $this->destroyAll($request);
        }

        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $notification->delete();

        return $this->successResponse('Notification deleted');
    }

    /**
     * Delete all notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyAll(Request $request)
    {
        // Delete all notifications for the user directly
        $request->user()->notifications()->delete();

        return $this->successResponse('All notifications deleted');
    }

    /**
     * Get notification preferences.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPreferences(Request $request)
    {
        // This would typically fetch from a notification_preferences table
        // For now, we'll return default preferences
        return response()->json([
            'preferences' => [
                'email' => [
                    'booking_confirmation' => true,
                    'event_reminder' => true,
                    'event_cancellation' => true,
                    'payment_confirmation' => true,
                    'refund_notification' => true,
                    'marketing' => false
                ],
                'database' => [
                    'booking_confirmation' => true,
                    'event_reminder' => true,
                    'event_cancellation' => true,
                    'payment_confirmation' => true,
                    'refund_notification' => true,
                    'marketing' => true
                ],
                'sms' => [
                    'booking_confirmation' => false,
                    'event_reminder' => false,
                    'event_cancellation' => true,
                    'payment_confirmation' => false,
                    'refund_notification' => false,
                    'marketing' => false
                ]
            ]
        ]);
    }

    /**
     * Update notification preferences.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.email' => 'array',
            'preferences.database' => 'array',
            'preferences.sms' => 'array'
        ]);

        // This would typically update a notification_preferences table
        // For now, we'll just return success
        return $this->successResponse('Notification preferences updated');
    }
}

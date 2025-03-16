<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingTransferController extends Controller
{
    public function initiateTransfer(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed bookings can be transferred'
            ], 400);
        }

        if ($booking->checked_in_at) {
            return response()->json([
                'message' => 'Cannot transfer a checked-in booking'
            ], 400);
        }

        if ($booking->transfer_status === 'pending') {
            return response()->json([
                'message' => 'A transfer is already pending for this booking'
            ], 400);
        }

        // Generate transfer code and set expiry
        $transferCode = strtoupper(Str::random(8));
        $expiresAt = now()->addHours(24);

        $booking->update([
            'transfer_code' => $transferCode,
            'transfer_status' => 'pending',
            'transfer_initiated_at' => now(),
            'transfer_expires_at' => $expiresAt
        ]);

        return response()->json([
            'message' => 'Transfer initiated successfully',
            'transfer_code' => $transferCode,
            'expires_at' => $expiresAt->toIso8601String()
        ]);
    }

    public function acceptTransfer(Request $request)
    {
        $request->validate([
            'transfer_code' => 'required|string'
        ]);

        $booking = Booking::with(['event', 'ticketType'])
            ->where('transfer_code', $request->transfer_code)
            ->where('transfer_status', 'pending')
            ->where(function ($query) {
                $query->whereNull('transfer_expires_at')
                    ->orWhere('transfer_expires_at', '>', now());
            })
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'Invalid or expired transfer code'
            ], 404);
        }

        if ($booking->user_id === $request->user()->id) {
            return response()->json([
                'message' => 'Cannot transfer booking to yourself'
            ], 400);
        }

        try {
            $booking->transfer($request->user());

            // Make sure to load the relationships after the transfer
            $updatedBooking = $booking->fresh()->load(['event', 'ticketType']);

            return response()->json([
                'message' => 'Transfer completed successfully',
                'booking' => $updatedBooking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function cancelTransfer(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->transfer_status !== 'pending') {
            return response()->json([
                'message' => 'No pending transfer to cancel'
            ], 400);
        }

        $booking->update([
            'transfer_code' => null,
            'transfer_status' => null,
            'transfer_initiated_at' => null,
            'transfer_expires_at' => null
        ]);

        return response()->json([
            'message' => 'Transfer cancelled successfully'
        ]);
    }
}

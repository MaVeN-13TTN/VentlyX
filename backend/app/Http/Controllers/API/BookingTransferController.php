<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BookingTransferController extends Controller
{
    public function initiateTransfer(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized to transfer this booking'], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json(['message' => 'Only confirmed bookings can be transferred'], 400);
        }

        $transferCode = strtoupper(Str::random(8));

        $booking->update([
            'transfer_code' => $transferCode,
            'transfer_initiated_at' => now(),
            'transfer_status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Transfer initiated successfully',
            'transfer_code' => $transferCode,
            'expires_at' => $booking->transfer_initiated_at->addHours(24)
        ]);
    }

    public function acceptTransfer(Request $request)
    {
        $request->validate([
            'transfer_code' => ['required', 'string', 'size:8']
        ]);

        $booking = Booking::where('transfer_code', strtoupper($request->transfer_code))
            ->where('transfer_status', 'pending')
            ->where('transfer_initiated_at', '>', now()->subHours(24))
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $booking->update([
                'user_id' => $request->user()->id,
                'transfer_status' => 'completed',
                'transfer_completed_at' => now(),
                'transfer_code' => null
            ]);

            // Re-generate QR code for new owner
            $booking->generateQrCode();

            DB::commit();

            return response()->json([
                'message' => 'Transfer completed successfully',
                'booking' => $booking->load('event', 'ticketType')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancelTransfer(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized to cancel this transfer'], 403);
        }

        if ($booking->transfer_status !== 'pending') {
            return response()->json(['message' => 'No pending transfer to cancel'], 400);
        }

        $booking->update([
            'transfer_code' => null,
            'transfer_initiated_at' => null,
            'transfer_status' => null
        ]);

        return response()->json([
            'message' => 'Transfer cancelled successfully'
        ]);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscountCodeController extends Controller
{
    /**
     * Get all discount codes for an event
     */
    public function index(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);
        
        // Check if user is authorized to access discount codes
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $discountCodes = DiscountCode::where('event_id', $eventId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['discount_codes' => $discountCodes]);
    }
    
    /**
     * Create a new discount code
     */
    public function store(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);
        
        // Check if user is authorized to create discount codes
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'min_ticket_count' => ['nullable', 'integer', 'min:1'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        
        // Generate a code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::random(8));
        }
        
        // Create the discount code
        $discountCode = DiscountCode::create([
            'event_id' => $eventId,
            'code' => $validated['code'],
            'discount_type' => $validated['discount_type'],
            'discount_amount' => $validated['discount_amount'],
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'min_ticket_count' => $validated['min_ticket_count'] ?? 1,
            'max_discount' => $validated['max_discount'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);
        
        return response()->json([
            'message' => 'Discount code created successfully',
            'discount_code' => $discountCode
        ], 201);
    }
    
    /**
     * Get a specific discount code
     */
    public function show(Request $request, string $eventId, string $id)
    {
        $event = Event::findOrFail($eventId);
        
        // Check if user is authorized
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $discountCode = DiscountCode::where('event_id', $eventId)
            ->where('id', $id)
            ->firstOrFail();
            
        return response()->json(['discount_code' => $discountCode]);
    }
    
    /**
     * Update a discount code
     */
    public function update(Request $request, string $eventId, string $id)
    {
        $event = Event::findOrFail($eventId);
        
        // Check if user is authorized
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $discountCode = DiscountCode::where('event_id', $eventId)
            ->where('id', $id)
            ->firstOrFail();
            
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50'],
            'discount_type' => ['nullable', 'in:fixed,percentage'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'min_ticket_count' => ['nullable', 'integer', 'min:1'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        
        $discountCode->update($validated);
        
        return response()->json([
            'message' => 'Discount code updated successfully',
            'discount_code' => $discountCode->fresh()
        ]);
    }
    
    /**
     * Delete a discount code
     */
    public function destroy(Request $request, string $eventId, string $id)
    {
        $event = Event::findOrFail($eventId);
        
        // Check if user is authorized
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $discountCode = DiscountCode::where('event_id', $eventId)
            ->where('id', $id)
            ->firstOrFail();
            
        $discountCode->delete();
        
        return response()->json(['message' => 'Discount code deleted successfully']);
    }
    
    /**
     * Validate a discount code
     */
    public function validate(Request $request, string $eventId)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'ticket_count' => ['nullable', 'integer', 'min:1'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
        ]);
        
        $discountCode = DiscountCode::where('event_id', $eventId)
            ->where('code', $request->code)
            ->first();
            
        if (!$discountCode || !$discountCode->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired discount code'
            ], 400);
        }
        
        $ticketCount = $request->ticket_count ?? 1;
        $subtotal = $request->subtotal ?? 0;
        
        if ($ticketCount < $discountCode->min_ticket_count) {
            return response()->json([
                'valid' => false,
                'message' => "This code requires a minimum of {$discountCode->min_ticket_count} tickets"
            ], 400);
        }
        
        $discount = $discountCode->calculateDiscount($subtotal, $ticketCount);
        
        return response()->json([
            'valid' => true,
            'discount_code' => $discountCode,
            'discount_amount' => $discount,
            'message' => 'Discount code applied successfully'
        ]);
    }
    
    /**
     * Bulk create discount codes
     */
    public function bulkStore(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);
        
        // Check if user is authorized
        if ($request->user()->id !== $event->organizer_id && !$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:100'],
            'prefix' => ['nullable', 'string', 'max:10'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'min_ticket_count' => ['nullable', 'integer', 'min:1'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        
        $count = $request->count;
        $prefix = $request->prefix ?? '';
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $code = $prefix . strtoupper(Str::random(8));
            
            $codes[] = DiscountCode::create([
                'event_id' => $eventId,
                'code' => $code,
                'discount_type' => $request->discount_type,
                'discount_amount' => $request->discount_amount,
                'starts_at' => $request->starts_at ?? null,
                'expires_at' => $request->expires_at ?? null,
                'max_uses' => $request->max_uses ?? null,
                'min_ticket_count' => $request->min_ticket_count ?? 1,
                'max_discount' => $request->max_discount ?? null,
                'is_active' => $request->is_active ?? true,
            ]);
        }
        
        return response()->json([
            'message' => "{$count} discount codes created successfully",
            'discount_codes' => $codes
        ], 201);
    }
}
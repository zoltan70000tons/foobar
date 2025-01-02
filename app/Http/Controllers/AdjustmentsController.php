<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Adjustment;
use App\Models\Booking;

class AdjustmentsController extends Controller
{
    /**
     * Create a new adjustment and associate it with a booking.
     *
     * @param  int  $bookingId
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAdjustment(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:adjustments,code',
            'type' => 'required|string|in:DISCOUNT,ADDON',
            'operation' => 'required|string|in:FIXED,PERCENTAGE',
            'value' => 'required|numeric|min:0',
            'restrictions' => 'nullable|json',
            'event_id' => 'required|integer|exists:events,id',
            'booking_id' => 'required|integer|exists:bookings,id'
        ]);

        // Verify if the booking exists
        $booking = Booking::findOrFail($validated['booking_id']);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the adjustment record
            $adjustment = Adjustment::create([
                'code' => $validated['code'],
                'type' => $validated['type'],
                'operation' => $validated['operation'],
                'value' => $validated['value'],
                'restrictions' => $validated['restrictions'] ?? null,
                'event_id' => $validated['event_id'],
            ]);

            // Link the adjustment to the booking
            DB::table('booking_has_adjustments')->insert([
                'booking_id' => $booking->id,
                'adjustment_id' => $adjustment->id,
            ]);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Adjustment created and linked successfully.',
                'adjustment' => $adjustment,
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create adjustment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

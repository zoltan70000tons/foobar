<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Adjustment;
use App\Models\Booking;
use App\Traits\BookingLogTrait;
use App\Traits\HandlePermissions;
use Inertia\Inertia;

class AdjustmentsController extends Controller
{
    /**
     * Create a new adjustment and associate it with a booking.
     *
     * @param  int  $bookingId
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    use HandlePermissions;
    use BookingLogTrait;
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

            $this->withPermission([Permissions::CreateTaxes], function ($validated, $booking) {
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
                $this->saveBookingLog($booking->id, 'Added Adjustment', 'Added '. $adjustment->type.' '.$adjustment->operation.' '.' with value '.$adjustment->value );
    
                return redirect()->route('bookings.show', [
                    'id' => $validated['event_id'],
                    'booking_code' => $booking->booking_code,
                ])->with([
                    'message' => 'Adjustment created and linked successfully.',
                ]);
            },  $validated,$booking);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create adjustment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteAdjustment(Request $request)
    {

        $validated = $request->validate([
            'id' => 'required|integer|exists:adjustments,id',
            'event_id' => 'required|integer|exists:events,id',
            'booking_id' => 'required|integer|exists:bookings,id'
        ]);

       try {
        $this->withPermission([Permissions::DeleteTaxes], function ($validated) {
            $adjustment = Adjustment::findOrFail($validated['id']);
            $booking = Booking::findOrFail($validated['booking_id']);
            if ($adjustment->event_id !== $validated['event_id']) {
                return response()->json([
                    'message' => 'The adjustment does not match the specified event or booking.'
                ], 403); 
            }
            $adjustment->delete();
            $this->saveBookingLog($booking->id, 'Deleted Adjustment', 'Deleted '. $adjustment->type.' '.$adjustment->operation.' '.' with value '.$adjustment->value );
            return redirect()->route('bookings.show', [
                'id' => $validated['event_id'],
                'booking_code' => $booking->booking_code,
            ])->with([
                'message' => 'Adjustment deleted successfully.',
            ]);
        },  $validated);
       } catch (\Exception $ex) {
        dd($ex->getMessage());
       }
    
    }



    public function updateAdjustment(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'id' => 'required|integer|exists:adjustments,id',
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

            $this->withPermission([Permissions::EditTaxes], function ($validated, $booking) {
                $adjustment = Adjustment::findOrFail($validated['id']);
                $adjustment->update([
                    'code' => $validated['code'],
                    'type' => $validated['type'],
                    'operation' => $validated['operation'],
                    'value' => $validated['value'],
                    'restrictions' => $validated['restrictions'] ?? null,
                    'event_id' => $validated['event_id'],
                ]);
                // Commit the transaction
                DB::commit();
                $this->saveBookingLog($booking->id, 'Updated Adjustment', 'Updated '. $adjustment->type.' '.$adjustment->operation.' '.' with value '.$adjustment->value );
                return redirect()->route('bookings.show', [
                    'id' => $validated['event_id'],
                    'booking_code' => $booking->booking_code,
                ])->with([
                    'message' => 'Adjustment updated and linked successfully.',
                ]);
            },  $validated,$booking);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to updated adjustment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Traits\ExceptionLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Adjustment;
use App\Models\Booking;
use App\Traits\BookingLogTrait;
use App\Traits\HandlePermissions;
use Illuminate\Validation\Rule;
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
    use ExceptionLogger;
    public function createAdjustment(Request $request)
    {
       
        $booking_id = $request->route('booking_id');
        $event_id = $request->route('event_id');
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:adjustments,code',
            'type' => 'required|string|in:DISCOUNT,ADDON',
            'operation' => 'required|string|in:FIXED,PERCENTAGE',
            'value' => 'required|numeric|min:0',
            'restrictions' => 'nullable|json'
        ]);
  
        // Verify if the booking exists
        $booking = Booking::findOrFail($booking_id);

        // Start a database transaction
        DB::beginTransaction();

        try {

            return $this->withPermission([Permissions::CreateAdjustments], function ($validated, $booking,$event_id) {
                $adjustment = Adjustment::create([
                    'code' => $validated['code'],
                    'type' => $validated['type'],
                    'operation' => $validated['operation'],
                    'value' => $validated['value'],
                    'restrictions' => $validated['restrictions'] ?? null,
                    'event_id' => $event_id,
                ]);
    
                // Link the adjustment to the booking
                DB::table('booking_has_adjustments')->insert([
                    'booking_id' => $booking->id,
                    'adjustment_id' => $adjustment->id,
                ]);
    
                // Commit the transaction
                DB::commit();
                $this->saveBookingLog($booking->id, 'Added Adjustment', 'Added '. $adjustment->type.' '.$adjustment->operation.' '.' with value '.$adjustment->value );
                return redirect()->back()->with('success', 'Adjustment created and linked successfully.');
            },  $validated,$booking,$event_id);
        } catch (\Exception $e) {
             
            dd($e->getMessage());
            // Rollback the transaction on error
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create adjustment.');
        }
    }


    public function deleteAdjustment(Request $request)
    {
        $booking_id = $request->route('booking_id');
        $event_id = (int)$request->route('event_id');
        $validated = $request->validate([
            'id' => 'required|integer|exists:adjustments,id',
        ]);

       try {
       return $this->withPermission([Permissions::DeleteAdjustments], function ($validated,$event_id,$booking_id) {
            $adjustment = Adjustment::findOrFail($validated['id']);
            $booking = Booking::findOrFail($booking_id);
            if ($adjustment->event_id !== $event_id) {
                return redirect()->back()->with('error', 'The adjustment does not match the specified event or booking.');
            }
            $result = $adjustment->delete();
            $this->saveBookingLog($booking->id, 'Deleted Adjustment', 'Deleted '. $adjustment->type.' '.$adjustment->operation.' '.' with value '.$adjustment->value );
            return redirect()->back()->with('error', 'Deleted Adjustment.');
        },  $validated,$event_id,$booking_id);
       } catch (\Exception $e) {
        $this->logException($e);
        return redirect()->back()->with('error', 'Error deleting adjustment.');
       }
    
    }



    public function updateAdjustment(Request $request)
    {
        $booking_id = $request->route('booking_id');
        $event_id = $request->route('event_id');
    
        // Start a database transaction
        DB::beginTransaction();
    
        try {
            // Verify if the booking exists
            $booking = Booking::findOrFail($booking_id);
    
            // Find the adjustment to update
            $adjustment = Adjustment::findOrFail($request->input('id'));
    
            // Validate the incoming request
            $validated = $request->validate([
                'code' => [
                    'required',
                    Rule::unique('adjustments')->ignore($adjustment->id),
                ],
                'type' => 'required|string',
                'operation' => 'required|string',
                'value' => 'required|numeric',
                'restrictions' => 'nullable|array',
            ]);
    
            // Verify permission before updating
            $this->withPermission([Permissions::EditAdjustments], function () use ($validated, $adjustment, $event_id) {
                $adjustment->update([
                    'code' => $validated['code'],
                    'type' => $validated['type'],
                    'operation' => $validated['operation'],
                    'value' => $validated['value'],
                    'restrictions' => $validated['restrictions'] ?? null,
                    'event_id' => $event_id,
                ]);
            });
    
            // Commit the transaction
            DB::commit();
    
            // Save booking log
            $this->saveBookingLog(
                $booking->id,
                'Updated Adjustment',
                'Updated ' . $adjustment->type . ' ' . $adjustment->operation . ' with value ' . $adjustment->value
            );
    
            return redirect()->route('bookings.show', [
                'id' => $event_id,
                'booking_code' => $booking->booking_code,
            ])->with([
                'message' => 'Adjustment updated and linked successfully.',
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
    
            return response()->json([
                'message' => 'Failed to update adjustment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

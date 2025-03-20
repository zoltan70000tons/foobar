<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Booking;
use App\Models\PassengerDiscount;
use App\Traits\BookingLogTrait;
use Illuminate\Http\Request;
use App\Repositories\CalculationRepository;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Facades\DB;

class DiscountsController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    use BookingLogTrait;

    protected PassengerRepository $passengerRepository;
    protected CalculationRepository $calculationRepository;
    public function __construct(PassengerRepository $passengerRepository, CalculationRepository $calculationRepository)
    {
        $this->passengerRepository = $passengerRepository;
        $this->calculationRepository =$calculationRepository;
    }

    public function store(Request $request)
    {
        try {
            return $this->withPermission([Permissions::CreatePassengerDiscounts], function ($request) {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'passenger_id' => 'required|exists:passengers,id',
                    'amount' => 'required|numeric|min:0.01',
                    'operation' => 'required|string|in:FIXED,PERCENTAGE',
                    'type' => 'required|string|max:255',

                ]);
                PassengerDiscount::create($validated);
                $this->calculationRepository->recalculateAllocatedCost($validated['passenger_id'], $booking_id, $event_id);
                $this->saveBookingLog(
                    $booking_id,
                    'Added Manual Discount',
                    "Manual Discount {$validated['type']} value: \${$validated['amount']} was added to booking"
                );
                return redirect()->back()->with('success', 'Fee added successfully!');
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);
            return redirect()->back()->with('error', 'Error creating fee!');
        }
    }

    public function delete(Request $request)
    {
        return $this->withPermission([Permissions::DeletePassengerDiscounts], function ($request) {
            DB::beginTransaction();
            try {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'discount_id' => 'required|exists:passenger_discounts,id',
                    'passenger_id' => 'required|exists:passengers,id',
                ]);
                $booking = Booking::where('id', $booking_id)
                    ->where('event_id', $event_id)
                    ->first();
                if (!$booking) {
                    return redirect()->back()->with('error', 'Booking not found.');
                }
                $discount = PassengerDiscount::where('id', $validated['discount_id'])
                    ->where('passenger_id', $validated['passenger_id'])
                    ->first();
                if (!$discount) {
                    return redirect()->back()->with('error', 'Discount not found.');
                }
                $amount = $discount->amount;
                $type = $discount->type;
                $discount->delete();
                $this->calculationRepository->recalculateAllocatedCost($validated['passenger_id'], $booking_id, $event_id);

                DB::commit();

                $this->saveBookingLog(
                    $booking_id,
                    'Deleted discount from passenger',
                    "Discount: {$type} value: \${$amount} was deleted"
                );

                return redirect()->back()->with('success', 'Discount deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);

                return redirect()->back()->with('error', 'Discount deleting fee!');
            }
        }, $request);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Booking;
use App\Models\Fee;
use App\Models\Installment;
use App\Traits\BookingLogTrait;
use Illuminate\Http\Request;
use App\Repositories\CalculationRepository;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentInfoService;

class FeeController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    use BookingLogTrait;

    protected PassengerRepository $passengerRepository;
    protected PaymentInfoService $paymentInfoService;

    public function __construct(PassengerRepository $passengerRepository, PaymentInfoService $paymentInfoService)
    {
        $this->passengerRepository = $passengerRepository;
        $this->paymentInfoService =$paymentInfoService;
    }

    public function store(Request $request)
    {
        try {
            return $this->withPermission([Permissions::CreateFees], function ($request) {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'passenger_id' => 'required|exists:passengers,id',
                    'amount' => 'required|numeric|min:0.01',
                    'type' => 'required|string|max:255',

                ]);
                Fee::create($validated);
                $this->paymentInfoService->syncAllocatedCost(Booking::find($booking_id));
                $this->saveBookingLog(
                    $booking_id,
                    'Added Manual Fee',
                    "Manual {$validated['type']} value: \${$validated['amount']} was added to booking"
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
        return $this->withPermission([Permissions::DeleteFees], function ($request) {
            DB::beginTransaction();
            try {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'fee_id' => 'required|exists:fees,id',
                    'passenger_id' => 'required|exists:passengers,id',
                ]);
                $booking = Booking::where('id', $booking_id)
                    ->where('event_id', $event_id)
                    ->first();
                if (!$booking) {
                    return redirect()->back()->with('error', 'Booking not found.');
                }
                $fee = Fee::where('id', $validated['fee_id'])
                    ->where('passenger_id', $validated['passenger_id'])
                    ->first();
                if (!$fee) {
                    return redirect()->back()->with('error', 'Fee not found.');
                }
                $amount = $fee->amount;
                $type = $fee->type;
                $installment = Installment::where('fee_id', $fee->id)->first();
                $installment->delete();
                $fee->delete();
                $this->paymentInfoService->syncAllocatedCost($booking);

                DB::commit();

                $this->saveBookingLog(
                    $booking_id,
                    'Deleted Fee',
                    "Fee: {$type} value: \${$amount} was deleted"
                );

                return redirect()->back()->with('success', 'Fee deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);

                return redirect()->back()->with('error', 'Error deleting fee!');
            }
        }, $request);
    }
}

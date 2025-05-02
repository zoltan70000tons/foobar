<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Booking;
use App\Models\OnboardCredit;
use App\Models\PassengerDiscount;
use App\Traits\BookingLogTrait;
use Illuminate\Http\Request;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentInfoService;

class OnboardCreditController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    use BookingLogTrait;

    protected PassengerRepository $passengerRepository;
    protected PaymentInfoService $paymentInfoService;

    public function __construct(PassengerRepository $passengerRepository, PaymentInfoService $paymentInfoService)
    {
        $this->passengerRepository = $passengerRepository;
        $this->paymentInfoService = $paymentInfoService;
    }

    public function store(Request $request)
    {
        return $this->withPermission([Permissions::CreatePassengerOnboardCredit], function ($request) {
            DB::beginTransaction();

            try {
                $booking_id = $request->route('booking_id');
                //$event_id = $request->route('event_id'); //FIXME currently unused
                $validated = $request->validate([
                    'passenger_id' => 'required|exists:passengers,id',
                    'amount' => 'required|numeric|min:0.01',
                    'reason' => 'required|string|max:255',

                ]);
                OnboardCredit::create($validated);

                DB::commit();

                $this->saveBookingLog(
                    $booking_id,
                    'Added Onboard Credit',
                    "Manual Onboard Credit was added to booking"
                );

                return redirect()->back()->with('success', 'Onboard Credit added successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);

                return redirect()->back()->with('error', 'Error creating onboard credit!');
            }
        }, $request);
    }

    public function delete(Request $request)
    {
        return $this->withPermission([Permissions::DeletePassengerOnboardCredit], function ($request) {
            DB::beginTransaction();
            try {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'onboard_credit_id' => 'required|exists:onboard_credit,id',
                    'passenger_id' => 'required|exists:passengers,id',
                ]);
                $booking = Booking::where('id', $booking_id)
                    ->where('event_id', $event_id)
                    ->first();

                if (!$booking) {
                    return redirect()->back()->with('error', 'Booking not found.');
                }

                $onboardCredit = OnboardCredit::where('id', $validated['onboard_credit_id'])
                    ->where('passenger_id', $validated['passenger_id'])
                    ->first();

                if (!$onboardCredit) {
                    return redirect()->back()->with('error', 'Onboard credit not found.');
                }

                $onboardCredit->delete();

                DB::commit();

                $this->saveBookingLog(
                    $booking_id,
                    'Deleted onboard credit from passenger',
                    "Id: {$validated['onboard_credit_id']} was deleted"
                );

                return redirect()->back()->with('success', 'Onboard credit deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);

                return redirect()->back()->with('error', 'Discount deleting onboard credit!');
            }
        }, $request);
    }
}

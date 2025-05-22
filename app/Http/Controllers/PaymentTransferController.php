<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Booking;
use App\Models\OnboardCredit;
use App\Models\PassengerDiscount;
use App\Models\Payment;
use App\Models\PaymentTransfer;
use App\Services\PaymentTransferService;
use App\Traits\BookingLogTrait;
use Illuminate\Http\Request;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentInfoService;

class PaymentTransferController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    use BookingLogTrait;

    protected PassengerRepository $passengerRepository;
    protected PaymentInfoService $paymentInfoService;
    protected PaymentTransferService $paymentTransferService;

    public function __construct(PassengerRepository $passengerRepository, PaymentInfoService $paymentInfoService,
                                PaymentTransferService $paymentTransferService)
    {
        $this->passengerRepository = $passengerRepository;
        $this->paymentInfoService = $paymentInfoService;
        $this->paymentTransferService = $paymentTransferService;
    }

    public function store(Request $request)
    {
        return $this->withPermission([Permissions::CreatePayments], function ($request) {
            DB::beginTransaction();

            try {
                $booking_id = $request->route('booking_id');
                //$event_id = $request->route('event_id'); //FIXME currently unused
                $validated = $request->validate([
                    'passenger_id' => 'required|exists:passengers,id',
                    'amount' => 'required|numeric|min:0.01',
                    'transfer_to_passenger' => 'required|exists:passengers,id',

                ]);

                $this->paymentTransferService->transferPayment($validated);

                DB::commit();

                $this->saveBookingLog(
                    $booking_id,
                    'Added Payment Transfer',
                    "Added Payment Transfer"
                );

                $booking = Booking::find($booking_id);

                $this->paymentInfoService->syncBalance($validated["passenger_id"], $booking->id, $booking->event_id);
                $this->paymentInfoService->syncBalance($validated["transfer_to_passenger"], $booking->id, $booking->event_id);

                return redirect()->back()->with('success', 'Payment Transfer added successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);
                dd($e->getMessage());

                return redirect()->back()->with('error', 'Error creating payment transfer!');
            }
        }, $request);
    }

    public function delete(Request $request)
    {
        return $this->withPermission([Permissions::DeletePayments], function ($request) {
            DB::beginTransaction();
            try {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'transfer_id' => 'required|exists:payment_transfers,id',
                ]);

                $booking = Booking::where('id', $booking_id)
                    ->where('event_id', $event_id)
                    ->first();

                if (!$booking) {
                    return redirect()->back()->with('error', 'Booking not found.');
                }

                $paymentTransfer = PaymentTransfer::query()
                    ->where('id', $validated['transfer_id'])
                    ->first();

                if (!$paymentTransfer) {
                    return redirect()->back()->with('error', 'Payment transfer not found.');
                }

                Payment::query()->where('id', $paymentTransfer->payment_id_from)
                    ->delete();
                Payment::query()->where('id', $paymentTransfer->payment_id_to)
                    ->delete();

                $paymentTransfer->delete();

                DB::commit();

                $this->saveBookingLog(
                    $booking_id,
                    'Deleted payment transfer from passengers',
                    "Id: {$validated['transfer_id']} was deleted"
                );

                return redirect()->back()->with('success', 'Payment transfer deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);

                return redirect()->back()->with('error', 'Failed to delete payment transfer!');
            }
        }, $request);
    }
}

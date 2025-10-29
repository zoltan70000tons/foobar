<?php

namespace App\Http\Controllers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Enums\Permissions;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentTransfer;
use App\Services\PaymentTransferService;
use App\Support\GlobalLogger;
use Illuminate\Http\Request;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentInfoService;

class PaymentTransferController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;

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
                $validated = $request->validate([
                    'passenger_id' => 'required|exists:passengers,id',
                    'amount' => 'required|numeric|min:0.01',
                    'transfer_to_passenger' => 'required|exists:passengers,id',

                ]);

                $this->paymentTransferService->transferPayment($validated);

                DB::commit();

                $booking = Booking::find($booking_id);

                $this->paymentInfoService->syncBalance($validated["passenger_id"], $booking->id, $booking->event_id);
                $this->paymentInfoService->syncBalance($validated["transfer_to_passenger"], $booking->id, $booking->event_id);

                return redirect()->back()->with('success', 'Payment Transfer added successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);

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

                $fromPayment = Payment::find($paymentTransfer->payment_id_from);
                $toPayment = Payment::find($paymentTransfer->payment_id_to);

                if ($fromPayment) {
                    $fromPayment->delete();
                }

                if ($toPayment) {
                    $toPayment->delete();
                }

                $paymentTransfer->delete();

                GlobalLogger::log(
                    LogActionBooking::TRANSFER_PAYMENT_DELETED,
                    'booking',
                    $booking->id,
                    'Transfer deleted between passengers',
                    [
                        'after' => [
                            'from_payment' => [
                                'payment_id' => $fromPayment->id,
                                'passenger_id' => $fromPayment->passenger_id,
                                'amount' => $fromPayment->amount,
                                'notes' => $fromPayment->notes,
                                'transaction_id' => $fromPayment->BIP_ID,
                            ],
                            'to_payment' => [
                                'payment_id' => $toPayment->id,
                                'passenger_id' => $toPayment->passenger_id,
                                'amount' => $toPayment->amount,
                                'notes' => $toPayment->notes,
                                'transaction_id' => $toPayment->BIP_ID,
                            ],
                            'booking_id' => $booking->id,
                            'booking_request_id' => $booking->booking_request_id,
                        ],
                    ]
                );

                DB::commit();

                return redirect()->back()->with('success', 'Payment transfer deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);

                return redirect()->back()->with('error', 'Failed to delete payment transfer!');
            }
        }, $request);
    }
}

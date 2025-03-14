<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Booking;
use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use App\Traits\BookingLogTrait;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Repositories\CalculationRepository;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Laravel\SerializableClosure\SerializableClosure;



class PaymentController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    use BookingLogTrait;

    protected PassengerRepository $passengerRepository;
    protected CalculationRepository $calculationRepository;

    protected PaymentService $paymentService;
    public function __construct(PassengerRepository $passengerRepository, CalculationRepository $calculationRepository, PaymentService $paymentService)
    {
        $this->passengerRepository = $passengerRepository;
        $this->calculationRepository = $calculationRepository;
        $this->paymentService = $paymentService;
    }

    public function store(Request $request)
    {
        try {
            return $this->withPermission([Permissions::CreateFees], function ($request) {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'passenger_id' => 'required|exists:passengers,id',
                    'BIP_ID' => 'nullable|string|max:50',
                    'amount' => 'required|numeric|min:0.01',
                    'type' => 'required|in:PAYMENT,REFUND',
                    'notes' => 'nullable|string|max:255',
                    'transaction_date' => 'required|date'
                ]);
                $validated['source'] = 'MANUAL';
                $this->paymentService->processPayment($validated);
                //Payment::create($validated);
                $this->calculationRepository->recalculateBalance($validated['passenger_id'], $booking_id, $event_id);
                $this->saveBookingLog(
                    $booking_id,
                    'Added Manual Payment',
                    "Manual {$validated['type']} value: \${$validated['amount']} was added to booking"
                );
                return redirect()->back()->with('success', 'Payment added successfully!');
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);
            return redirect()->back()->with('error', 'Error creating payment!');
        }
    }

    public function delete(Request $request)
    {
        try {
            return $this->withPermission([Permissions::DeletePayments], function ($request) {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'payment_id' => 'required|exists:payments,id',
                    'passenger_id' => 'required|exists:passengers,id',
                ]);
                $booking = Booking::where('id', $booking_id)
                    ->where('event_id', $event_id)
                    ->first();
                if (!$booking) {
                    return redirect()->back()->with('error', 'Booking not found.');
                }
                $payment = Payment::where('id', $validated['payment_id'])
                    ->where('passenger_id', $validated['passenger_id'])
                    ->first();
                if (!$payment) {
                    return redirect()->back()->with('error', 'Fee not found.');
                }
                $amount = $payment->amount;
                $type =$payment->type;
                $payment->delete();
                $balance = $this->calculationRepository->recalculateBalance($validated['passenger_id'], $booking_id, $event_id);
                $this->saveBookingLog(
                    $booking_id,
                    'Deleted payment',
                    "Payment: {$type} value: \${$amount} was deleted"
                );
                return redirect()->back()->with('success', 'payment deleted successfully!');
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);
            return redirect()->back()->with('error', 'Error deleting payment!');
        }
    }

    public function createPayment(Request $request)
    {
        dd($this->paymentService->getInstallmentsWithStatus(1));
        //$response = $this->paymentService->processPayment($request->all());
        //return response()->json($response, $response['success'] ? 201 : 400);
    }
}

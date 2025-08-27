<?php

namespace App\Http\Controllers;

use App\Enums\PaymentType;
use App\Enums\Permissions;
use App\Exceptions\InvalidBipIdException;
use App\Models\Booking;
use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use App\Traits\BookingLogTrait;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Laravel\SerializableClosure\SerializableClosure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\PaymentInfoService;

class PaymentController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    use BookingLogTrait;

    protected PassengerRepository $passengerRepository;
    protected PaymentInfoService $paymentInfoService;

    protected PaymentService $paymentService;
    public function __construct(PassengerRepository $passengerRepository, PaymentService $paymentService, PaymentInfoService $paymentInfoService)
    {
        $this->passengerRepository = $passengerRepository;
        $this->paymentService = $paymentService;
        $this->paymentInfoService =$paymentInfoService;
    }

    public function store(Request $request): Response|RedirectResponse
    {
        return $this->withPermission([Permissions::CreateFees], function ($request) {
            DB::beginTransaction();

            try {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validator = Validator::make($request->all(), [
                    'passenger_id' => 'required|exists:passengers,id',
                    'BIP_ID' => 'required|string|max:50',
                    'amount' => 'required|numeric|min:0.01',
                    'type' => 'required|in:PAYMENT,REFUND',
                    'notes' => 'nullable|string|max:255',
                    'transaction_date' => 'required|date'
                ]);

                if ($validator->fails()) {
                    if ($validator->errors()->has('BIP_ID')) {
                        throw new InvalidBipIdException();
                    }
                }
                $validated = $validator->validated();
                $validated['source'] = 'MANUAL';
                Payment::create($validated);
                $this->paymentInfoService->syncBalance($validated['passenger_id'], $booking_id, $event_id);

                DB::commit();

                $this->saveBookingLog(
                    $booking_id,
                    'Added Manual Payment',
                    "Manual {$validated['type']} value: \${$validated['amount']} was added to booking"
                );

                $type = PaymentType::from($validated['type']);

                return redirect()->back()->with('success', $type->getLabel() . ' added successfully!');
            } catch (InvalidBipIdException $e) {
                DB::rollBack();
                $this->logException($e);

                return $e->render($request);
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);

                return redirect()->back()->with('error', 'Error creating payment!');
            }
        }, $request);
    }

    public function delete(Request $request): Response|RedirectResponse
    {
        return $this->withPermission([Permissions::DeletePayments], function ($request) {
            DB::beginTransaction();

            try {
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
                    return redirect()->back()->with('error', 'Payment not found.');
                }
                $amount = $payment->amount;
                $type = $payment->type;

                $splitAmount = $payment->splitAmount;
                if ($splitAmount) {
                    $transactionId = $payment->BIP_ID;

                    $payments = Payment::query()
                        ->where("BIP_ID", "=", $transactionId)
                        ->get();

                    foreach ($payments as $payment) {
                        $passengerId = $payment->passenger_id;

                        $payment->delete();
                        $this->paymentInfoService->syncBalance($passengerId, $booking_id, $event_id);
                    }

                    DB::commit();

                    $this->saveBookingLog(
                        $booking_id,
                        'Deleted split payment',
                        "Transaction ID: {$transactionId} was deleted"
                    );
                } else {
                    $payment->delete();
                    $this->paymentInfoService->syncBalance($validated['passenger_id'], $booking_id, $event_id);

                    DB::commit();

                    $this->saveBookingLog(
                        $booking_id,
                        'Deleted payment',
                        "Payment: {$type} value: \${$amount} was deleted"
                    );
                }

                return redirect()->back()->with('success', 'Payment deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);

                return redirect()->back()->with('error', 'Error deleting payment!');
            }
        }, $request);
    }

    public function createPayment(Request $request)
    {
        dd($this->paymentService->getInstallmentsWithStatus(1));
        //$response = $this->paymentService->processPayment($request->all());
        //return response()->json($response, $response['success'] ? 201 : 400);
    }
}

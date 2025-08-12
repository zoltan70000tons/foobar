<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Booking;
use App\Models\OnboardCredit;
use App\Models\PassengerDiscount;
use App\Models\Payment;
use App\Models\PaymentTransfer;
use App\Services\PaymentTransferService;
use App\Services\SplitPaymentService;
use App\Traits\BookingLogTrait;
use Illuminate\Http\Request;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentInfoService;
use Illuminate\Support\Facades\Validator;

class SplitPaymentController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    use BookingLogTrait;

    protected PassengerRepository $passengerRepository;
    protected PaymentInfoService $paymentInfoService;
    protected SplitPaymentService $splitPaymentService;

    public function __construct(PassengerRepository $passengerRepository, PaymentInfoService $paymentInfoService,
                                SplitPaymentService $splitPaymentService)
    {
        $this->passengerRepository = $passengerRepository;
        $this->paymentInfoService = $paymentInfoService;
        $this->splitPaymentService = $splitPaymentService;
    }

    public function store(Request $request)
    {
        return $this->withPermission([Permissions::CreatePayments], function ($request) {
            DB::beginTransaction();

            try {
                $formData = $request->get('sanitizedFormData');
                $passengerData = array_filter(
                    $formData,
                    fn($value, $key) => str_starts_with($key, 'passenger_'),
                    ARRAY_FILTER_USE_BOTH
                );
                $totalLeftToPay = $formData['totalLeftToPay'];
                $totalPaymentAdded = $formData['totalPaymentAdded'];

                $booking_id = $request->route('booking_id');

                $errors = [];
                $validPassengers = [];

                foreach ($passengerData as $key => $passenger) {
                    $validator = Validator::make($passenger, [
                        'passengerId' => 'required|exists:passengers,id',
                        'amount' => 'required|numeric',
                    ]);

                    if ($validator->fails()) {
                        $errors[$key] = $validator->errors();
                    } else {
                        $validPassengers[$passenger['passengerId']] = $passenger;
                    }
                }

                if (!empty($errors)) {
                    return back()->withErrors($errors)->withInput();
                }

                $booking = Booking::find($booking_id);

                foreach ($validPassengers as $passenger) {
                    $this->splitPaymentService->registerSplitPayment($passenger);

                    $this->paymentInfoService->syncBalance($passenger["passengerId"], $booking->id, $booking->event_id);
                }



                $this->saveBookingLog(
                    $booking_id,
                    'Added Split Payment',
                    "Added Split Payment. Total payment added: {$totalPaymentAdded}. Total left to pay: {$totalLeftToPay}"
                );

                DB::commit();

                return redirect()->back()->with('success', 'Split payment added successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logException($e);
                dd($e->getMessage());

                return redirect()->back()->with('error', 'Error creating split payment!');
            }
        }, $request);
    }
}

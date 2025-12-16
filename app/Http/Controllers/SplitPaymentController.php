<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Booking;
use App\Models\Passenger;
use App\Services\SplitPaymentService;
use Illuminate\Http\Request;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentInfoService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SplitPaymentController extends Controller
{
  use HandlePermissions;
  use ExceptionLogger;

  protected PassengerRepository $passengerRepository;
  protected PaymentInfoService $paymentInfoService;
  protected SplitPaymentService $splitPaymentService;

  public function __construct(
    PassengerRepository $passengerRepository,
    PaymentInfoService $paymentInfoService,
    SplitPaymentService $splitPaymentService
  ) {
    $this->passengerRepository = $passengerRepository;
    $this->paymentInfoService = $paymentInfoService;
    $this->splitPaymentService = $splitPaymentService;
  }

  public function store(Request $request)
  {
    return $this->withPermission(
      [Permissions::CreatePayments],
      function ($request) {
        DB::beginTransaction();

        try {
          $formData = $request->get('sanitizedFormData');

          $transactionId = $request->get('transactionId');
          if (!$transactionId) {
            $transactionId = Str::uuid()->toString();
          }

          $passengerData = array_filter(
            $formData,
            fn($value, $key) => str_starts_with($key, 'passenger_'),
            ARRAY_FILTER_USE_BOTH
          );

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

          $countSaved = 0;

          foreach ($validPassengers as $passenger) {
            if ($passenger['amount'] === 0) {
              continue;
            }

            $pax = Passenger::findOrFail($passenger['passengerId']);
            $totalLeftToPay = $pax->passenger_allocated_cost - $pax->passenger_balance;
            if ($passenger['amount'] > $totalLeftToPay) {
              $passenger['amount'] = $totalLeftToPay;
            }

            $this->splitPaymentService->registerSplitPayment($passenger, $transactionId);

            $this->paymentInfoService->syncBalance($passenger['passengerId'], $booking->id, $booking->event_id);

            ++$countSaved;
          }

          if ($countSaved === 0) {
            return redirect()->back()->with('warning', 'No payment to split!');
          }

          DB::commit();

          return redirect()->back()->with('success', 'Split payment added successfully!');
        } catch (\Exception $e) {
          DB::rollBack();
          $this->logException($e);

          return redirect()->back()->with('error', 'Error creating split payment!');
        }
      },
      $request
    );
  }
}

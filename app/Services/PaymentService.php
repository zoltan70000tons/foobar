<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\Passenger;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Carbon\Carbon;
use Exception;
use Log;
use Validator;

class PaymentService
{
  protected $paymentRepository;

  public function __construct(PaymentRepository $paymentRepository)
  {
    $this->paymentRepository = $paymentRepository;
  }

  /*
  |--------------------------------------------------------------------------
  | Calculate due dates for installment payments
  |--------------------------------------------------------------------------
  |
  |  This method will calculate the due dates for the installment payments
  |
  */
  public function createInstallments(int $passengerId, int $numberOfInstallments): void
  {
    $installments = [];

    $passenger = Passenger::find($passengerId);
    if (!$passenger) {
      throw new \InvalidArgumentException('Passenger not found');
    }

    $booking = $passenger->booking;
    if (!$booking || !$booking->event || !$booking->event->start_date) {
      throw new \InvalidArgumentException('Associated booking or event start date not found');
    }

    $baseDate = Carbon::now();
    // Use booking created_at if present and booking was just created (consistency with previous behavior)
    if ($passenger->booking && $passenger->booking->created_at) {
      $baseDate = Carbon::parse($passenger->booking->created_at);
    }

    $lastAllowed = Carbon::parse($booking->event->start_date)->subWeek();

    // Compute maximum number of monthly installments we can fit before lastAllowed (max 5)
    $allowedMax = 0;
    for ($i = 0; $i < 5; $i++) {
      $due = $baseDate->copy()->addMonths($i);
      if ($due->lte($lastAllowed)) {
        $allowedMax = $i + 1;
      } else {
        break;
      }
    }

    // Ensure at least 2 installments are possible
    if ($allowedMax < 2 && $numberOfInstallments >= 2) {
      throw new \InvalidArgumentException("Not enough time to $numberOfInstallments installments before event cutoff (one week before start).");
    }

    $count = min($numberOfInstallments, $allowedMax);

    for ($i = 0; $i < $count; $i++) {
      $installments[] = $baseDate->copy()->addMonths($i)->toDateString();
    }

    $this->paymentRepository->createInstallments($passengerId, $installments);
  }


  public function processPayment($data)
  {
    $validator = Validator::make($data, [
      'passenger_id' => 'required|exists:passengers,id',
      'bip_id' => 'nullable|string|max:50',
      'type' => 'required|in:PAYMENT,REFUND',
      'amount' => 'required|numeric|min:0.01',
      'source' => 'required|in:MANUAL,SYSTEM',
      'notes' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
      return ['success' => false, 'message' => $validator->errors()->first()];
    }

    try {
      $passenger = Passenger::findOrFail($data['passenger_id']);
      $booking = $passenger->booking;
      $passengerAllocatedCost = $passenger->passenger_allocated_cost;

      if ($booking->payment_plan === "INSTALLMENTS") {
        $unpaidInstallments = Installment::where('passenger_id', $passenger->id)
          ->whereDoesntHave('payments', function ($q) {
            $q->where('installment_payment.status', 'PAID');
          })
          ->get();

        if ($unpaidInstallments->isEmpty()) {
          return ['success' => false, 'message' => 'No unpaid installments available'];
        }

        $remainingAmount = $data['amount'];
        $payment = Payment::create([
          'passenger_id' => $data['passenger_id'],
          'BIP_ID' => $data['bip_id'] ?? null,
          'type' => $data['type'],
          'transaction_date' => now(),
          'amount' => $data['amount'],
          'source' => $data['source'],
          'notes' => $data['notes'] ?? null,
        ]);

        foreach ($unpaidInstallments as $installment) {
          if ($remainingAmount <= 0) {
            break;
          }
          $installmentAmount = $passengerAllocatedCost / $unpaidInstallments->count();
          $dueAmount = $installmentAmount - $installment->payments()->sum('amount_paid');
          $amountToPay = min($remainingAmount, $dueAmount);
          $remainingAmount -= $amountToPay;
          $installment->payments()->attach($payment->id, [
            'amount_paid' => $amountToPay,
            'status' => ($amountToPay == $dueAmount) ? 'PAID' : 'PARTIALLY_PAID',
            'created_at' => now(),
            'updated_at' => now(),
          ]);
        }

        return ['success' => true, 'payment' => $payment];
      } elseif ($booking->payment_plan === "PAY_IN_FULL") {
        $totalPaid = Payment::where('passenger_id', $passenger->id)->sum('amount');
        $remainingBalance = $passengerAllocatedCost - $totalPaid;

        if ($remainingBalance <= 0) {
          return ['success' => false, 'message' => 'Passenger allocated cost already fully paid'];
        }

        $paymentAmount = min($data['amount'], $remainingBalance);
        $payment = Payment::create([
          'passenger_id' => $data['passenger_id'],
          'BIP_ID' => $data['bip_id'] ?? null,
          'type' => $data['type'],
          'transaction_date' => now(),
          'amount' => $paymentAmount,
          'source' => $data['source'],
          'notes' => $data['notes'] ?? null,
        ]);

        return ['success' => true, 'payment' => $payment];
      }
      return ['success' => false, 'message' => 'Invalid payment plan'];
    } catch (Exception $e) {
      Log::error("Error processing payment: " . $e->getMessage());
      return ['success' => false, 'message' => 'Failed to process payment'];
    }
  }

  public function getInstallmentsWithStatus($passengerId)
  {
    try {
      $passenger = Passenger::find($passengerId);
     // dd($passenger);
      if (!$passenger) {
        return response()->json(['success' => false, 'message' => 'Passenger not found'], 404);
      }
      return $passenger->paymentInfo;
     
    } catch (Exception $e) {
      dd($e->getMessage());
      Log::error("Error fetching installments: " . $e->getMessage());
      return ['success' => false, 'message' => 'Failed to fetch installments'];
    }
  }
}

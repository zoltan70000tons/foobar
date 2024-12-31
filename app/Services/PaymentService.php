<?php

namespace App\Services;

use App\Repositories\PaymentRepository;
use Carbon\Carbon;

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
    $currentDate = Carbon::now();

    for ($i = 0; $i < $numberOfInstallments; $i++) {
      $installments[] = $currentDate->copy()->addMonths($i)->toDateString();
    }

    $this->paymentRepository->createInstallments($passengerId, $installments);
  }
}

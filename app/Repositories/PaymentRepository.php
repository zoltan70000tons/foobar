<?php

namespace App\Repositories;

use App\Models\Adjustment;
use App\Models\Booking;
use App\Models\Fee;
use App\Models\Installment;
use Log;

class PaymentRepository {
    protected $installment;

    public function __construct(Installment $installment) {
        $this->installment = $installment;
    }

    /**
     * Create installments for a passenger.
     *
     * @param int $passengerId
     * @param array $installments
     * @return void
     */
    public function createInstallments(int $passengerId, array $installments): void {
        foreach ($installments as $dueDate) {
            $this->installment->create([
                'passenger_id' => $passengerId,
                'due_date' => $dueDate,
            ]);
        }
    }
}

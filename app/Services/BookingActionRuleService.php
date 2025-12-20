<?php
// app/Services/BookingActionRuleService.php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingActionRule;
use App\Models\Fee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Str;

class BookingActionRuleService
{
    protected $paymentInfoService;

    public function __construct(
        PaymentInfoService $paymentInfoService
    ) {
        $this->paymentInfoService = $paymentInfoService;
    }
    public function applyPassengerActionRule(
        int $eventId,
        string $actionCode,
        int $passengerId,
        $bookingId
    ): Collection {
        $appliedFees = collect();

        $rules = BookingActionRule::where('event_id', $eventId)
            ->where('action_code', $actionCode)
            ->active()
            ->where('fee_amount', '>', 0)
            ->get();

        if ($rules->isEmpty()) {
            return $appliedFees;
        }

        foreach ($rules as $rule) {
            $fee = Fee::create([
                'passenger_id' => $passengerId,
                'type' => Str::limit(
                    $rule->description ?? $rule->action_code,
                    50
                ),
                'amount' => $rule->fee_amount,
                'notes' => Str::limit(
                    'Applied via action rule ' . $actionCode . ' - ' . ($rule->description ?? ''),
                    150
                ),
            ]);

            $appliedFees->push($fee);

            Log::info('Fee created', [
                'passenger_id' => $passengerId,
                'action_rule_id' => $rule->id,
                'amount' => $rule->fee_amount,
            ]);
        }

        $this->paymentInfoService->syncAllocatedCost(Booking::find($bookingId));

        return $appliedFees;
    }
}

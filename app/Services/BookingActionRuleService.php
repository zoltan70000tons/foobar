<?php
// app/Services/BookingActionRuleService.php

namespace App\Services;

use App\Models\BookingActionRule;
use App\Models\Fee;
use Illuminate\Support\Collection;
use Str;

class BookingActionRuleService {
    public function applyPassengerActionRule(int $eventId, string $actionCode, int $passengerId): Collection {
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
                'type' => Str::limit($rule->description ?? $rule->action_code, 50),
                'amount' => $rule->fee_amount,
                'notes' => Str::limit(
                    'Applied via action rule ' . $actionCode . ' - ' . ($rule->description ?? ''),
                    150,
                ),
            ]);

            $appliedFees->push($fee);
        }

        return $appliedFees;
    }
}

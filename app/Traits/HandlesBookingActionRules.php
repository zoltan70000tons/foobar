<?php
// app/Traits/HandlesBookingActionRules.php

namespace App\Traits;

use App\Models\BookingActionRule;

trait HandlesBookingActionRules
{
    public function getActionRule(string $eventId, string $actionCode): ?BookingActionRule
    {
        return BookingActionRule::where('event_id', $eventId)
            ->where('action_code', $actionCode)
            ->active()
            ->first();
    }

    public function isActionAllowed(string $eventId, string $actionCode): bool
    {
        $rule = $this->getActionRule($eventId, $actionCode);

        return !$rule || !$rule->is_blocking;
    }
}


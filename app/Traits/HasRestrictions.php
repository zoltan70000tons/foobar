<?php

namespace App\Traits;

use App\Helpers\RestrictionEvaluator;

trait HasRestrictions {
    public function shouldApply(array $context = []): bool {
        $restrictions = $this->restrictions;

        // Handle both string and array
        if (is_string($restrictions)) {
            $restrictions = json_decode($restrictions, true);
        }

        return RestrictionEvaluator::evaluate($restrictions, $context);
    }
}

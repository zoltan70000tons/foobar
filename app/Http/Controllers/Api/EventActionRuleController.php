<?php

// app/Http/Controllers/Api/EventActionRuleController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\BookingActionRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EventActionRuleController extends Controller
{
    public function show(Event $event, string $actionCode): JsonResponse
    {
        $rule = BookingActionRule::where('event_id', $event->id)
            ->where('action_code', $actionCode)
            ->active()
            ->first();

        if (!$rule) {
            return response()->json([
                'allowed' => true,
            ]);
        }

        return response()->json([
            'allowed'     => !$rule->is_blocking,
            'is_blocking' => $rule->is_blocking,
            'fee_amount'  => $rule->fee_amount,
            'description' => $rule->description,
        ]);
    }
}

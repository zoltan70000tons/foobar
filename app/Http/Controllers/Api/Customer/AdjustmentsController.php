<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adjustment;

class AdjustmentsController extends Controller {
    /**
     * Show adjustments for a booking
     *
     */
    public function show(Request $request, $id) {
        $adjustments = Adjustment::where('event_id', $id)->get();
        if (!$adjustments) {
            return response()->json(['message' => 'No adjustments found for this booking'], 404);
        }

        return response()->json($adjustments);
    }
}

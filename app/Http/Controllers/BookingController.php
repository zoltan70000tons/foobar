<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bed;

class BookingController extends Controller
{
    public function bookBed(Request $request)
    {
        $bed = Bed::where('status', 'available')->first();

        if ($bed) {
            $bed->status = 'booked';
            $bed->save();

            return response()->json(['message' => 'Bed booked successfully!', 'bed' => $bed]);
        }

        return response()->json(['message' => 'No available beds'], 404);
    }
}

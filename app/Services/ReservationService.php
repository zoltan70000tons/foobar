<?php

namespace App\Services;

use App\Models\TemporaryReservation;
use Illuminate\Http\Request;

class ReservationService
{
  public function releaseCabin(Request $request)
  {
    if (!$request->session()->has('reserved_cabin_id')) {
      return ['status' => 404, 'message' => 'No cabin reserved'];
    }

    $reservationId = $request->session()->get('reserved_cabin_id');
    $reservation = TemporaryReservation::find($reservationId);

    if (!$reservation) {
      return ['status' => 404, 'message' => 'No reservation found'];
    }

    if ($request->user() && $reservation->user_id !== $request->user()->id) {
      return ['status' => 403, 'message' => 'Unauthorized'];
    }

    $reservation->delete();
    $request->session()->forget(['reserved_cabin_id', 'cart']);

    return ['status' => 200, 'message' => 'Cabin released'];
  }
}

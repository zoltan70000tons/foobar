<?php

namespace App\Services;

use App\Models\TemporaryReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

class ReservationService
{
  public function releaseCabin($user)
  {

    $reservation = TemporaryReservation::where('user_id', $user->id)->first();

    if (!$reservation) {
      return ['status' => 404, 'message' => 'No reservation found for user'];
    }

    $reservation->delete();

    return ['success' => true, 'message' => 'Cabin released'];
  }
}

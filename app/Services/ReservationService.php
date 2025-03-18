<?php

namespace App\Services;

use App\Models\TemporaryReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

class ReservationService
{
  public function releaseCabin(Request $request)
  {
    $user = Auth::user();

    if ($user) {
      // Authenticated user: Remove temporary reservation & clear cart
      $reservation = TemporaryReservation::where('user_id', $user->id)->first();

      if (!$reservation) {
        return ['status' => 404, 'message' => 'No reservation found for user'];
      }

      $reservation->delete();
      Cart::where('user_id', $user->id)->delete(); // Clear cart from DB
    } else {
      // Guest user: Remove session-based reservation
      if (!$request->session()->has('reserved_cabin_id')) {
        return ['status' => 404, 'message' => 'No cabin reserved'];
      }

      $reservationId = $request->session()->get('reserved_cabin_id');
      $reservation = TemporaryReservation::find($reservationId);

      if (!$reservation) {
        return ['status' => 404, 'message' => 'No reservation found'];
      }

      $reservation->delete();
      $request->session()->forget(['reserved_cabin_id', 'cart']); // Clear session cart
    }

    return ['status' => 200, 'message' => 'Cabin released and cart cleared'];
  }
}

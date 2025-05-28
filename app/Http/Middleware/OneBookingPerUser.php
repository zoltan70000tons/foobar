<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Cart;
use App\Services\ReservationService;


use Closure;

class OneBookingPerUser
{
  public function handle($request, Closure $next)
  {
    if (Auth::check()) {
      $user = Auth::user();

      $bookingExists = Booking::where('event_id', $request->event_id)
        ->where('status', '!=', 'CANCELLED')
        ->where(function ($query) use ($user) {
          $query->where('customer_id', $user->id)->orWhereHas('passengers', function ($q) use ($user) {
            $q->where('survivor_number', $user->survivorNumber->survivor_number);
          });
        })
        ->exists();

      if ($bookingExists) {
        $reservationService = new ReservationService();

        $cart = Cart::where('user_id', $user->id)->first();
      
        if ($cart) {
          $cart->delete();
          $reservationService->releaseCabin($request);
        }

        return response()->json(
          [
            'message' => __('bookings.already_has_booking'),
            'code' => 'BOOKING_LIMIT_EXCEEDED',
          ],
          403
        );
      }
    }

    return $next($request);
  }
}

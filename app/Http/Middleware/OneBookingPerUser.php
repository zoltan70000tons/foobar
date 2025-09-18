<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Cart;
use App\Services\ReservationService;
use App\Enums\ErrorCode;
use Illuminate\Support\Str;
use App;
use App\Enums\ErrorCode;
use Illuminate\Support\Str;


use Closure;

class OneBookingPerUser
{
  public function handle($request, Closure $next)
  {
    if (Auth::check()) {
      $user = Auth::user();
      $language = $request->input('language', 'en');
      App::setLocale($language);

      $survivorNumber = $user->survivorNumber?->survivor_number;

      $bookingExists = Booking::where('event_id', $request->event_id)
        ->where('status', '!=', 'CANCELLED')
        ->where(function ($query) use ($user, $survivorNumber) {
          $query->where('customer_id', $user->id);

          if ($survivorNumber) {
            $query->orWhereHas('passengers', function ($q) use ($survivorNumber) {
              $q->where('survivor_number', $survivorNumber);
            });
          }
        })
        ->exists();

      if ($bookingExists) {
        $reservationService = new ReservationService();

        $cart = Cart::where('user_id', $user->id)->first();
      
        if ($cart) {
          $cart->delete();
          $reservationService->releaseCabin($user);
        }

        return response()->json(
          [
            'errorLogId' => 'backend_middleware per user ' . Str::uuid(),
            'errorMessage' => __('bookings.already_has_booking'),
            'errorCode' => ErrorCode::BOOKING_LIMIT_EXCEEDED->value,
          ],
          403
        );
      }
    }

    return $next($request);
  }
}

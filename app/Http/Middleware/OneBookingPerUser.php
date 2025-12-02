<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\PassengerInvitation;
use App\Models\Cart;
use App\Services\ReservationService;
use App\Enums\ErrorCode;
use Illuminate\Support\Str;
use App;

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

      $invitationExist = PassengerInvitation::where('email', $user->email)
        ->whereHas('booking', function ($q) use ($request) {
          $q->where('event_id', $request->event_id)->where('status', '!=', 'CANCELLED');
        })
        ->exists();

      if ($bookingExists || $invitationExist) {
        $reservationService = new ReservationService();

        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
          $cart->delete();
          $reservationService->releaseCabin($user);
        }

        // Determine the appropriate error message
        $errorMessage = __('bookings.already_has_booking');
        if ($invitationExist) {
          $errorMessage = __('bookings.already_has_invitation');
        }

        return response()->json(
          [
            'errorLogId' => 'backend_middleware per user ' . Str::uuid(),
            'errorMessage' => $errorMessage,
            'errorCode' => ErrorCode::BOOKING_LIMIT_EXCEEDED->value,
          ],
          403
        );
      }
    }

    return $next($request);
  }
}

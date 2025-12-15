<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use App\Enums\ErrorCode;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

/**
 * Helper class for creating standardized error responses
 */
class ErrorResponse
{
  /**
   * Return a generic error response
   *
   * @param string $message
   * @param ErrorCode $errorCode
   * @param int $httpStatusCode
   * @return \Illuminate\Http\JsonResponse
   */
  public static function error(string $message, ErrorCode $errorCode, int $httpStatusCode = 400)
  {
    return response()->json(
      [
        'errorLogId' => Str::uuid(),
        'errorMessage' => $message,
        'errorCode' => $errorCode->value,
      ],
      $httpStatusCode
    );
  }

  /**
   * Return a reservation expired error response and clear the user's cart
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public static function reservationExpired()
  {
    // Clear the user's cart
    Cart::where('user_id', Auth::id())->delete();

    return self::error(
      'Your reservation has expired. Please select your cabin again.',
      ErrorCode::RESERVATION_EXPIRED,
      410 // HTTP - Resource no longer available
    );
  }

  /**
   * Return a cart empty or not found error response
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public static function cartEmpty()
  {
    return self::error(
      'Your cart is empty or not found. Please add items to your cart.',
      ErrorCode::CART_EMPTY,
      400 // HTTP - Not Found
    );
  }
}

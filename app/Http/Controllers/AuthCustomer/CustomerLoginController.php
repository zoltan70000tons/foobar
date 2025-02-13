<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use App\Models\SurvivorNumber;
use Illuminate\Support\Facades\Cookie;
use App\Services\EmailUniquenessService;

class CustomerLoginController extends Controller
{
  protected EmailUniquenessService $emailUniquenessService;

  public function __construct(EmailUniquenessService $emailUniquenessService)
  {
    $this->emailUniquenessService = $emailUniquenessService;
  }

  /**
   * Handle an incoming authentication request.
   */
  public function store(Request $request): JsonResponse
  {
    // Step 1: Validate the request data
    $request->validate([
      'identifier' => 'required|string',
      'password' => 'required|string',
      'remember' => 'boolean',
    ]);

    $identifier = $request->input('identifier');
    $password = $request->input('password');
    $remember = $request->input('remember', false);

    // Determine if the identifier is an email
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

    $email = $isEmail
      ? $identifier
      : optional(SurvivorNumber::where('survivor_number', $identifier)->first())->user->email;

    if ($email) {
      $uniquenessResponse = $this->emailUniquenessService->handleEmailUniqueness($email);
      if ($uniquenessResponse) {
        return $uniquenessResponse;
      }
    }

    // Step 2: Attempt to authenticate the activated user
    $credentials = [
      'email' => $email,
      'password' => $password
    ];

    if (Auth::attempt($credentials, $remember)) {
      $user = Auth::user();

      if ($user && $user->hasRole('Customer')) {
        $request->session()->regenerate();
        return response()->json($user, 200);
      }

      Auth::logout();
      return response()->json(['message' => __('auth.failed')], 403);
    }

    return response()->json(['message' => __('auth.failed')], 401);
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): JsonResponse
  {
    Auth::guard('web')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    // destroy emaIl_verified cookie
    $response = response()->json(null, 204);
    return $response->withCookie(Cookie::forget('email_verified'));

    //return response()->json(null, 204);
  }
}

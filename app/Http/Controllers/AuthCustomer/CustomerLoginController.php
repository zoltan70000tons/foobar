<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\SurvivorNumber;
use Illuminate\Support\Facades\Cookie;
use App\Models\TemporaryPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerLoginController extends Controller
{
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

    // if email is null return error
    if (!$email) {
      return response()->json(['message' => __('auth.failed')], 401);
    }

    // Step 2: Attempt to authenticate the activated user
    $credentials = [
      'email' => $email,
      'password' => $password,
    ];

    if (Auth::attempt($credentials, $remember)) {
      setPermissionsTeamId(1);

      $user = Auth::user();

      if ($user && $user->hasRole('Customer')) {
        $request->session()->regenerate();
        return response()->json($user, 200);
      }

      Auth::logout();
      return response()->json(['message' => __('auth.failed')], 403);
    }


    // Step 3: Check for temporary password
    $user = User::where('email', $email)->first();

    if ($user) {
        $validTempPassword = TemporaryPassword::where('customer_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->get()
            ->first(fn($record) => Hash::check($password, $record->temporary_password));

        if ($validTempPassword) {
            // Mark as used
            $validTempPassword->update(['used_at' => now()]);


            Auth::login($user, $remember);
            setPermissionsTeamId(1);

            $request->session()->regenerate();

            return response()->json($user, 200);
        }
    }


    return response()->json(['message' => __('auth.failed')], 401);
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): JsonResponse
  {
    Auth::guard('web')->logout();

    Cookie::queue(Cookie::forget('email_verified'));

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return response()->json(null, 204);
  }
}

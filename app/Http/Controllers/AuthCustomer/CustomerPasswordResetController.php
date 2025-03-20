<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerResetPasswordSuccess;
use Illuminate\Support\Facades\Log;
//use App\Services\EmailUniquenessService;

class CustomerPasswordResetController extends Controller
{
  // protected EmailUniquenessService $emailUniquenessService;

  // public function __construct(EmailUniquenessService $emailUniquenessService)
  // {
  //   $this->emailUniquenessService = $emailUniquenessService;
  // }

  /**
   * Password reset request
   *
   */
  public function requestReset(Request $request): JsonResponse
  {
    $request->validate([
      'email' => 'required|email',
      'language' => 'sometimes|string|in:en,es,fr', // Add supported languages
    ]);

    // Set the application locale if language is provided
    if ($request->has('language')) {
      App::setLocale($request->language);
    }

    // Check if the customer exists
    $user = User::where('email', $request->email)->first();

    if (!$user || !$user->hasRole('Customer')) {
      return response()->json(
        [
          'message' => __('passwords.user'),
        ],
        404
      );
    }

    $status = Password::broker('customers')->sendResetLink($request->only('email'));

    if ($status === Password::RESET_LINK_SENT) {
      return response()->json(
        [
          'message' => __('passwords.sent'),
        ],
        200
      );
    } else {
      return response()->json(
        [
          'message' => __('passwords.throttled'),
        ],
        429
      );
    }
  }

  /**
   * Reset password with token
   *
   */
  public function resetPassword(Request $request)
  {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
    ]);

    // Attempt to reset the password
    $status = Password::broker('customers')->reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function ($user) use ($request) {
        // Check if the user has the 'Customer' role
        if (!$user->hasRole('Customer')) {
          throw ValidationException::withMessages([
            'email' => [__('passwords.user')],
          ]);
        }

        $user
          ->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
          ])
          ->save();

        // Fire the password reset event
        event(new PasswordReset($user));
      }
    );

    $user = User::where('email', $request->email)->first();

    if ($status == Password::PASSWORD_RESET) {
      // Send a email to the customer the password was reset
      $this->sendUpdatePasswordEmail($user);

      return response()->json(
        [
          'message' => __('passwords.reset'),
        ],
        200
      );
    } else {
      return response()->json(
        [
          'message' => __('passwords.token'),
        ],
        400
      );
    }
  }

  /**
   * Send a email to the customer the password was reset.
   *
   * @param User $user
   * @return void
   */
  protected function sendUpdatePasswordEmail(User $user): void
  {
    try {
      $email = $user->email;
      Mail::to($email)->queue(new CustomerResetPasswordSuccess($user));
    } catch (\Exception $e) {
      Log::error('Failed to send welcome email to user ID ' . $user->id . ': ' . $e->getMessage());
    }
  }
}

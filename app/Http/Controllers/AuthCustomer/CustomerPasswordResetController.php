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
use App\Enums\ErrorCode;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CustomerPasswordResetController extends Controller {
    /*
  |--------------------------------------------------------------------------
  | Request password reset link FORGOT PASSWORD
  |--------------------------------------------------------------------------
  |
  | Handles sending a password reset link to the customer's email.
  |
  */
    public function requestReset(Request $request): JsonResponse {
        $request->validate([
            'email' => 'required|email',
            'lang' => 'sometimes|string|in:en,es,de',
        ]);

        $locale = $request->input('lang');
        // Set the application locale if language is provided
        if ($locale) {
            App::setLocale($locale);
        }

        // Check if the customer exists
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasRole('Customer')) {
            return $this->errorResponse(__('passwords.user'), ErrorCode::UNAUTHORIZED, 401);
        }

        $status = Password::broker('customers')->sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(
                [
                    'message' => __('passwords.sent'),
                ],
                200,
            );
        }

        if ($status === Password::RESET_THROTTLED) {
            return $this->errorResponse(__('passwords.throttled'), ErrorCode::TOO_MANY_REQUESTS, 429);
        }

        return $this->errorResponse(__('passwords.throttled'), ErrorCode::UNKNOWN_ERROR, 422);
    }

    /*
  |--------------------------------------------------------------------------
  | Check the reset token VALIDATE TOKEN
  |--------------------------------------------------------------------------
  |
  | before proceeding to reset password, the token should be validated
  |
  */
    public function validateResetToken(Request $request): JsonResponse {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasRole('Customer')) {
            return $this->errorResponse(__('passwords.user'), ErrorCode::UNAUTHORIZED, 401);
        }

        $tokenRecord = DB::table('password_reset_tokens')->where('email', $user->getEmailForPasswordReset())->first();

        if (!$tokenRecord || !Hash::check($request->token, $tokenRecord->token)) {
            return $this->errorResponse(__('passwords.token'), ErrorCode::INVALID_TOKEN, 400);
        }

        return response()->json(
            [
                'message' => __('passwords.valid'),
            ],
            200,
        );
    }

    /*
  |--------------------------------------------------------------------------
  | Reset Password RESET PASSWORD
  |--------------------------------------------------------------------------
  |
  | Handles resetting the customer's password.
  |
  */
    public function resetPassword(Request $request): JsonResponse {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'lang' => 'sometimes|string|in:en,es,de',
        ]);

        $locale = $request->input('lang');
        if ($locale) {
            App::setLocale($locale);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasRole('Customer')) {
            return $this->errorResponse(__('passwords.user'), ErrorCode::UNAUTHORIZED, 401);
        }

        $tokenRecord = DB::table('password_reset_tokens')->where('email', $user->getEmailForPasswordReset())->first();

        if (!$tokenRecord || !Hash::check($request->token, $tokenRecord->token)) {
            return $this->errorResponse(__('passwords.token'), ErrorCode::INVALID_TOKEN, 400);
        }

        $expiresIn = (int) config('auth.passwords.customers.expire', config('auth.passwords.users.expire', 60));
        $tokenExpired = Carbon::parse($tokenRecord->created_at)
            ->addMinutes($expiresIn)
            ->isPast();

        if ($tokenExpired) {
            DB::table('password_reset_tokens')->where('email', $user->getEmailForPasswordReset())->delete();

            return $this->errorResponse(__('passwords.expired'), ErrorCode::INVALID_TOKEN, 400);
        }

        $broker = Password::broker('customers');

        // Attempt to reset the password
        $status = $broker->reset($request->only('email', 'password', 'password_confirmation', 'token'), function (
            $user,
        ) use ($request) {
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
        });

        if ($status == Password::PASSWORD_RESET) {
            // Send a email to the customer the password was reset
            $this->sendUpdatePasswordEmail($user, $request->input('lang', 'en'));

            return response()->json(
                [
                    'message' => __('passwords.reset'),
                ],
                200,
            );
        }

        if ($status === Password::RESET_THROTTLED) {
            return $this->errorResponse(__('passwords.throttled'), ErrorCode::TOO_MANY_REQUESTS, 429);
        }

        return $this->errorResponse(__('passwords.token'), ErrorCode::INVALID_TOKEN, 400);
    }

    /**
     * Send a email to the customer the password was reset.
     *
     * @param User $user
     * @return void
     */
    protected function sendUpdatePasswordEmail(User $user, ?string $locale = null): void {
        try {
            $email = $user->email;
            $mailLocale = $locale ?? App::getLocale();
            Mail::to($email)->queue(new CustomerResetPasswordSuccess($user, $mailLocale));
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email to user ID ' . $user->id . ': ' . $e->getMessage());
        }
    }

    private function applyLocale(Request $request): void {
        $language = $request->input('lang');

        if (!empty($language)) {
            App::setLocale($language);
        }
    }

    private function errorResponse(string $message, ErrorCode $code, int $status): JsonResponse {
        return response()->json(
            [
                'errorLogId' => Str::uuid(),
                'errorMessage' => $message,
                'errorCode' => $code->value,
            ],
            $status,
        );
    }
}

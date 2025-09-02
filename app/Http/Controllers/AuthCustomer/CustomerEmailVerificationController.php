<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\User;
use App\Mail\CustomerVerificationEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class CustomerEmailVerificationController extends Controller
{


  /*
  |--------------------------------------------------------------------------
  | Index email verification
  |--------------------------------------------------------------------------
  |
  |  Return Inertia view for email verification.
  |
  */
  public function index(Request $request)
  {
    $language = $request->language;
    App::setLocale($language);

    if ($request->user()->hasVerifiedEmail()) {
      return redirect()->to(config('app.frontend_url') . '/en/login?verified=1');
    }

    // Render the Inertia view for email verification
    return Inertia::render('OAuth/EmailVerify', [
      'language' => $language,
      'user' => $request->user(),
    ]);
  }

  /*
  |--------------------------------------------------------------------------
  | Resend email verification
  |--------------------------------------------------------------------------
  |
  |  Resend the email verification link to the customer.
  |
  */
  public function reSend(Request $request): JsonResponse
  {
    $language = $request->language;
    App::setLocale($language);

    if ($request->user()->hasVerifiedEmail()) {
      return response()->json([
        'status' => 'already-verified',
        'message' => __('systemEmails.email_verified_already'),
      ]);
    }

    $user = User::findOrFail($request->user()->id);

    if ($user->hasVerifiedEmail()) {
      return response()->json([
        'status' => 'already-verified',
        'message' => __('systemEmails.email_verified_already'),
      ]);
    }

    // Generate verification link
    $verificationUrl = URL::temporarySignedRoute('verificationApi.verify', Carbon::now()->addMinutes(60), [
      'id' => $user->getKey(),
      'hash' => sha1($user->getEmailForVerification()),
    ]);

    Mail::to($user->email)->queue(new CustomerVerificationEmail($user, $verificationUrl, $language));
    // $request->user()->notify(new CustomerVerification());

    return response()->json([
      'status' => 'verification-link-sent',
      'message' => __('systemEmails.email_verification_link_sent'),
    ]);
  }

  /*
  |--------------------------------------------------------------------------
  | Handle email verification
  |--------------------------------------------------------------------------
  |
  |  Verify the email address of a customer.
  |
  */
  public function verify(Request $request): JsonResponse|RedirectResponse
  {
    $customer = User::findOrFail($request->route('id'));

    if (!hash_equals($request->route('hash'), sha1($customer->getEmailForVerification()))) {
      return redirect()->to(config('app.frontend_url') . '/en/login?verified=errorSignature');
    }

    // Log in the customer
    Auth::login($customer);
    // Regenerate session for security
    $request->session()->regenerate();

    if ($customer->hasVerifiedEmail()) {
      return redirect()->to(config('app.frontend_url') . '/en/login?verified=1');
    }

    $customer->markEmailAsVerified();

    // redirect to the customer dashboard in app
    return redirect()->to(config('app.frontend_url') . '/en');
  }
}

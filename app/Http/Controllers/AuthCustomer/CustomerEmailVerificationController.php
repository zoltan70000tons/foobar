<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\User;
use App\Notifications\CustomerEmailVerification;
use Illuminate\Support\Facades\Auth;

class CustomerEmailVerificationController extends Controller
{
  /**
   * Send a new email verification notification.
   */
  public function store(Request $request): JsonResponse
  {

    $language = $request->language;
    App::setLocale($language);

    if ($request->user()->hasVerifiedEmail()) {
      return response()->json([
        'status' => 'already-verified',
        'message' => __('systemEmails.email_verified_already'),
      ]);
    }

    $request->user()->notify(new CustomerEmailVerification());

    return response()->json([
      'status' => 'verification-link-sent',
      'message' => __('systemEmails.email_verification_link_sent'),
    ]);
  }

  /**
   * Handle email verification confirmation.
   */
  public function verify(Request $request): JsonResponse|RedirectResponse
  {

    $customer = User::findOrFail($request->route('id'));

    if (! hash_equals($request->route('hash'), sha1($customer->getEmailForVerification()))) {
      return redirect()->to(config('app.frontend_url') . '/en/login?verified=errorSignature');
    }

    if ($customer->hasVerifiedEmail()) {
      return redirect()->to(config('app.frontend_url') . '/en/login?verified=1');
    }

    $customer->markEmailAsVerified();

    // redirect to the customer dashboard in app
    return redirect()->to(config('app.frontend_url') . '/en/login?verified=1');
  }
}

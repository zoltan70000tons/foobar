<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;
use App\Models\CustomerPasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use App\Notifications\CustomerResetPassword;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\RedirectResponse;

class CustomerPasswordResetController extends Controller
{
  /**
   * Password reset request
   * 
   */
  public function requestReset(Request $request): JsonResponse
  {
    $request->validate([
      'survival_number' => [
        'required',
        'string',
        function ($attribute, $value, $fail) {
          if (!Customer::where('survival_number', $value)->orWhere('name', $value)->exists()) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
          }
        },
      ],
    ]);

    $language = $request->language;
    $survival_number = $request->survival_number;

    App::setLocale($language);

    $customer = Customer::where('survival_number', $survival_number)
      ->orWhere('name', $survival_number)
      ->first();

    // get email from customer
    $emails = $customer->email;

    if (!is_array($emails)) {
      $emails = [$emails];
    }

    // MODEL CustomerPasswordReset
    $token = CustomerPasswordReset::createToken($customer);

    // NOTIFY
    $customer->notify(new CustomerResetPassword($customer, $token));

    return response()->json([
      'message' => __('systemEmails.email_verification_link_sent'),
    ], 200);
  }


  /**
   * Token verification
   * 
   */
  public function verifyToken(Request $request): JsonResponse|RedirectResponse
  {

    $resetUrl = config('app.frontend_url') . "/en/password-reset/{$request->id}/{$request->token}";

    return redirect()->to($resetUrl);
  }

  /**
   * Reset password with token
   * 
   */
  public function resetPassword(Request $request)
  {

    $request->validate([
      'customer_id' => 'required|exists:customers,id',
      'token' => 'required|string',
      'password' => 'required|string|min:6|confirmed',
    ]);

    $customer_id = $request->customer_id;
    $token = $request->token;


    $language = $request->language;
    App::setLocale($language);

    $passwordReset = CustomerPasswordReset::where('customer_id', $customer_id)->first();

    // If the entry is not found, return an error
    if (!$passwordReset) {
      return response()->json(['message' => 'Invalid password reset token.'], 404);
    }

    // If the token is hashed, compare the hash
    if (!Hash::check($token, $passwordReset->token)) {
      return response()->json(['message' => 'Invalid password reset token.'], 403);
    }

    //$customer = $customerPasswordReset->customer;
    $customer = Customer::findOrFail($customer_id);
    $customer->password = Hash::make($request->password);
    $customer->save();

    // set password token to null
    $passwordReset->token = null;
    $passwordReset->save();


    return response()->json([
      'message' => __('auth.password_updated_successfully')
    ], 200);
  }
}

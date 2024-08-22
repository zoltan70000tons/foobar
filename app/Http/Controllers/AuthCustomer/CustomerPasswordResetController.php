<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use App\Notifications\CustomerResetPassword;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use App\Models\CustomerPasswordReset;

class CustomerPasswordResetController extends Controller
{
  /**
   * Password reset request
   * 
   */
  public function requestReset(Request $request): JsonResponse
  {
    $request->validate([
      'survivor_number' => [
        'required',
        'string',
        function ($attribute, $value, $fail) {
          if (!Customer::where('survivor_number', $value)->orWhere('name', $value)->exists()) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
          }
        },
      ],
    ]);

    $language = $request->language;
    $survivor_number = $request->survivor_number;

    App::setLocale($language);

    $customer = Customer::where('survivor_number', $survivor_number)
      ->orWhere('name', $survivor_number)
      ->first();

    // Generate a signed reset URL
    $temporarySignedUrl = URL::temporarySignedRoute(
      'passwordApi.verify',
      Carbon::now()->addMinutes(15),
      ['id' => $customer->id]
    );

    // Parse the query parameters from the signed URL
    $queryParams = parse_url($temporarySignedUrl, PHP_URL_QUERY);

    // Construct the full frontend URL, including the `id` parameter
    $resetUrl = config('app.frontend_url') . "/en/password-reset?id={$customer->id}&{$queryParams}";

    // Notify the customer with the reset link
    $customer->notify(new CustomerResetPassword($resetUrl));

    return response()->json([
      'message' => __('systemEmails.email_verification_link_sent'),
    ], 200);
  }

  public function verifyResetLink(Request $request)
  {
    // find customer by id
    $customerId = $request->route('id');
    $customer = Customer::findOrFail($customerId);

    if (!$customer) {
      return response()->json(['message' => 'Customer not found.'], 404);
    }

    // generate token and send it to the customer
    $token = $this->createToken($customer->id);

    return response()->json([
      'message' => 'Token generated successfully.',
      'token' => $token,
    ], 200);
  }

  /**
   * Reset password with token
   * 
   */
  public function resetPassword(Request $request)
  {

    $request->validate([
      'id' => 'required|exists:customers,id',
      'password' => 'required|string|min:6|confirmed',
    ]);

    $token = $request->input('token');

    $passwordReset = CustomerPasswordReset::where('token', $token)->first();

    if (!$passwordReset) {
      return response()->json([
        'message' => 'Invalid token.'
      ], 401);
    }

    $intId = (int)$request->input('id');

    $customer = Customer::findOrFail($intId);
    $customer->password = Hash::make($request->input('password'));
    $customer->save();

    // delete the token
    $passwordReset->delete();

    return response()->json([
      'message' => __('auth.password_updated_successfully')
    ], 200);
  }


  // token generate
  public function createToken($id)
  {
    $randomNum = Str::random(60);
    $token = Hash::make($randomNum);

    // add token to the table customer_password_resets
    CustomerPasswordReset::updateOrCreate(
      ['customer_id' => $id],
      [
        'token' => $token,
        'created_at' => Carbon::now(),
      ]
    );

    return $token;
  }
}

<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\App;

class CustomerAuthController extends Controller
{
  use HttpResponses;

  /**
   * Return the authenticated user
   * 
   */
  public function customer(Request $request)
  {
    // Get the authenticated customer by guard
    //$customer = Auth::user()->role === 'Customer' ? Auth::user() : null;
    $user = Auth::user();
    $customer = $user->hasRole('Customer') ? $user : null;

    if (!$customer) {
      return $this->errorResponse('Unauthorized', 401);
    }

    // check the customer have verified email
    if (!$customer->hasVerifiedEmail()) {
      return $this->errorResponse('Email not verified', 409);
    }

    // Get the first membership type of the customer
    $membership = $customer->membershipTypes->first() ?? null;
    
    return $this->successResponse([
      'name' => $customer->username,
      'membership_type' => $membership->name ?? null,
      'membership_discount' => $membership->discount_value ?? null,
      'email_verified_at' => $customer->email_verified_at ?? null,
    ]);
  }


  /**
   * Reset password with validated old one
   * 
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   * 
   */
  public function update(Request $request)
  {
    $language = $request->input('language', 'en');
    App::setLocale($language);

    if (!Hash::check($request->input('current_password'), $request->user()->password)) {
      return response()->json(['message' => __('auth.current_password_incorrect')], 422);
    }

    $validated = $request->validate([
      'password' => ['required', Password::defaults(), 'confirmed'],
    ]);

    // Update the user's password
    $request->user()->update([
      'password' => Hash::make($validated['password']),
    ]);

    return response()->json(['message' => __('auth.password_updated_successfully')]);
  }

}

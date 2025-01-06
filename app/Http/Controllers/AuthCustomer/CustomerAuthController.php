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
    $customer = $user->hasRole("Customer") ? $user : null;

    if (!$customer) {
      return $this->errorResponse("Unauthorized", 401);
    }

    // check the customer have verified email
    if (!$customer->hasVerifiedEmail()) {
      return $this->errorResponse("Email not verified", 409);
    }

    // Get the first membership type of the customer
    $membership = $customer->membershipTypes->first() ?? null;

    // Get the Customer Details
    $customerDetails = $customer->detail ?? null;

    // Get the first address of the customer
    $address = $customer->customerAddress ?? null;

    return $this->successResponse([
      "name" => $customer->detail->first_name ?? null,
      "membership_type" => $membership->name ?? null,
      "membership_discount" => $membership->discount_value ?? null,
      "email" => $customer->email,
      "email_verified_at" => $customer->email_verified_at ?? null,
      "survivor_number" => $customer->survivorNumber->survivor_number ?? null,
      "details" => $customerDetails ? $customerDetails->makeHidden(['user_id, id'])->toArray() : null,
      "address" => $address ? $address->makeHidden(['user_id, id'])->toArray() : null,
    ])->withCookie("email_verified", "true", 60);
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
    $language = $request->input("language", "en");
    App::setLocale($language);

    if (!Hash::check($request->input("current_password"), $request->user()->password)) {
      return response()->json(["message" => __("auth.current_password_incorrect")], 422);
    }

    $validated = $request->validate([
      "password" => ["required", Password::defaults(), "confirmed"],
    ]);

    // Update the user's password
    $request->user()->update([
      "password" => Hash::make($validated["password"]),
    ]);

    return response()->json(["message" => __("auth.password_updated_successfully")]);
  }

  /**
   * Update the profile of the authenticated user
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   *
   */
  public function updateProfile(Request $request)
  {
    $user = Auth::user();

    if (!$user->hasRole("Customer")) {
      return $this->errorResponse("Unauthorized", 401);
    }

    $validated = $request->validate([
      'phoneNumber' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
      'language' => ['required', 'string', 'max:5'],
      'country' => ['required', 'string', 'max:50'],
      'state' => ['nullable', 'string', 'max:20'],
      'addressLine1' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9 ]*$/'],
      'addressLine2' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9 ]*$/'],
      'city' => ['required', 'string', 'max:30'],
      'zipCode' => ['required', 'string', 'max:10'],
      'emergencyContactName' => ['required', 'string', 'max:75'],
      'emergencyPhoneNumber' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/', 'different:phoneNumber'],
    ]);

    // Update user details
    $user->detail()->updateOrCreate(
      ['user_id' => $user->id],
      [
        'language' => $validated['language'],
        'phone' => $validated['phoneNumber'],
        'emergency_c_name' => $validated['emergencyContactName'],
        'emergency_c_phone' => $validated['emergencyPhoneNumber'],
      ]
    );

    // Update or create customer address
    $user->customerAddress()->updateOrCreate(
      ['user_id' => $user->id],
      [
        'country' => $validated['country'],
        'state' => $validated['state'],
        'address_first' => $validated['addressLine1'],
        'address_second' => $validated['addressLine2'],
        'city' => $validated['city'],
        'postal_code' => $validated['zipCode'],
      ]
    );

    return $this->successResponse([
      'message' => 'Profile updated successfully.'
    ]);
  }
}

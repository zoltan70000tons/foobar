<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\App;
use App\Models\User;

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

    // Get the first membership type of the customer
    $membership = $customer->membershipTypes->first() ?? null;

    // Get the Customer Details
    $customerDetails = $customer->detail ?? null;

    // Get the first address of the customer
    $address = $customer->customerAddress ?? null;

    return $this->successResponse([
      'name' => $customer->detail->first_name ?? null,
      'membership_type' => $membership->name ?? null,
      'membership_discount' => $membership->discount_value ?? null,
      'email' => $customer->email,
      'email_verified_at' => $customer->email_verified_at ?? null,
      'survivor_number' => $customer->survivorNumber->survivor_number ?? null,
      'details' => $customerDetails ? $customerDetails->makeHidden(['user_id, id'])->toArray() : null,
      'address' => $address ? $address->makeHidden(['user_id, id'])->toArray() : null,
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

    if (!$user->hasRole('Customer')) {
      return $this->errorResponse('Unauthorized', 401);
    }

    $validated = $request->validate([
      'phoneNumber' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
      'language' => ['required', 'string', 'max:5', 'regex:/^[^<>!@#$%^&*+=]*$/'],
      'country' => ['required', 'string', 'max:50', 'regex:/^[^<>!@#$%^&*+=]*$/'],
      'state' => ['nullable', 'string', 'max:20', 'regex:/^[^<>!@#$%^&*+=]*$/'],
      'addressLine1' => ['required', 'string', 'max:50', 'regex:/^[^<>!@#$%^&*+=]*$/'],
      'addressLine2' => ['nullable', 'string', 'max:50', 'regex:/^[^<>!@#$%^&*+=]*$/'],
      'city' => ['required', 'string', 'max:30', 'regex:/^[^<>!@#$%^&*+=]*$/'],
      'zipCode' => ['required', 'string', 'max:10', 'regex:/^[^<>!@#$%^&*+=]*$/'],
      'emergencyContactName' => ['required', 'string', 'max:75'],
      'emergencyPhoneNumber' => ['required', 'string'],
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
      'message' => 'Profile updated successfully.',
    ]);
  }

  /**
   * Updates the login email address of the authenticated user
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   *
   */
  public function updateEmail(Request $request)
  {
    $validated = $request->validate([
      'new_email' => ['required', 'email', 'unique:users,email'],
    ]);

    $user = $request->user();

    // Update the email address
    $oldEmail = $user->email;
    $user->email = $validated['new_email'];
    $user->save();

    $language = $user->detail->language ?? 'en';

    // Send notification to the old email
    // Send notification email
    $this->sendEmailUpdateNotification($user, $oldEmail, $language);

    return response()->json(['message' => __('systemEmails.update_email.updated_successfully')]);
  }

  protected function sendEmailUpdateNotification(User $user, string $oldEmail, string $language): void
  {
    try {
      Mail::to($oldEmail)->send(new EmailUpdated($user, $language));
    } catch (\Exception $e) {
      Log::error('Failed to send email update notification to user ID ' . $user->id . ': ' . $e->getMessage());
    }
  }
}

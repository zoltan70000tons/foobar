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
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;
use App\Services\UserInfoService;

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
    
    \Log::info('CustomerAuthController: customer method called');
    
    $user = Auth::user();
    $customer = $user->hasRole('Customer') ? $user : null;

    \Log::info('CustomerAuthController: customer method called', ['customer' => $customer]);

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
  public function updateProfile(Request $request, UserInfoService $userInfoService)
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
      'addressLine1' => ['required', 'string', 'max:50', 'regex:/^[^<>!@$%^*+=]*$/'],
      'addressLine2' => ['nullable', 'string', 'max:50', 'regex:/^[^<>!@$%^*+=]*$/'],
      'city' => ['required', 'string', 'max:30', 'regex:/^[^<>!@$%^*+=]*$/'],
      'zipCode' => ['required', 'string', 'max:10', 'regex:/^[^<>!@$%^*+=]*$/'],
      'emergencyContactName' => ['required', 'string', 'max:75'],
      'emergencyPhoneNumber' => ['required', 'string'],
      'specialOptions' => ['nullable', 'array'],
      'specialRequest' => ['nullable', 'string'],
    ]);

    $result = $userInfoService->updateUserProfile($user, $validated);

    if ($result['success']) {
      return $this->successResponse(['message' => $result['message']]);
    }

    return $this->errorResponse($result['message'], 500);
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
    try {
      DB::beginTransaction();
      // Get the authenticated user
      $user = Auth::user();

      if (!$user->hasRole('Customer')) {
        return $this->errorResponse('Unauthorized', 401);
      }

      $validated = $request->validate([
        'new_email' => ['required', 'email', 'unique:users,email'],
      ]);

      // Update the email address
      $oldEmail = $user->email;
      $user->email = $validated['new_email'];
      $user->save();

      // Update passenger data for existing bookings ( NEW or ON HOLD)
      $bookings = $user->bookings()->whereIn('status', ['NEW', 'ON HOLD'])->get();

      foreach ($bookings as $booking) {
        foreach ($booking->passengers->where('survivor_number', $user->survivorNumber->survivor_number) as $passenger) {
          $passenger->update([
            'email' => $validated['new_email'],
          ]);
        }
      }
      DB::commit();

      $language = $user->detail->language ?? 'en';
      App::setLocale($language);

      // Send notification to the old email address
      $this->sendEmailUpdateNotification($user, $oldEmail, $language);

      return response()->json(['message' => __('systemEmails.update_email.subject')]);
    } catch (\Exception $e) {
      return $this->errorResponse('Unauthorized', 401);
    }
  }

  protected function sendEmailUpdateNotification(User $user, string $oldEmail, string $language): void
  {
    try {
      Mail::to($oldEmail)->queue(new EmailUpdated($user, $language));
    } catch (\Exception $e) {
      Log::error('Failed to send email update notification to user ID ' . $user->id . ': ' . $e->getMessage());
    }
  }



  /*
  |--------------------------------------------------------------------------
  | Check user cant delete account
  |--------------------------------------------------------------------------
  |
  | User cannot delete account if they have an active booking.
  */
  public function canDeleteAccount(Request $request)
  {
    $language = $request->input('language', 'en');
    App::setLocale($language);
    $user = $request->user();

    // Check if user has an active booking
    $hasActiveBooking = $user->bookings()->whereIn('status', ['NEW', 'ON HOLD'])->exists();

    return response()->json([
      'canDelete' => !$hasActiveBooking,
      'message' => $hasActiveBooking
        ? __('feedback.cannot_delete_account')
        : __('feedback.proceed_delete_account'),
    ]);
  }


  /**
   * Delete the authenticated user
   *
   * Set null to the user's email and password
   * Set null to the user's details
   *
   * @param  \Illuminate\Http\Request  $request
   *
   */
  public function deleteAccount(Request $request)
  {
    $user = Auth::user();

    if (!$user->hasRole('Customer')) {
      return $this->errorResponse('Unauthorized', 401);
    }

    // if user have bookings, we don't allow to delete account
    $hasActiveBooking = $user->bookings()->whereIn('status', ['NEW', 'ON HOLD'])->exists();
    if ($hasActiveBooking) {
      return $this->errorResponse('You cannot delete your account while you have an active booking.', 403);
    }

    // Anonymize email to prevent duplicate uniqueness constraint issues
    $user->update([
      'email' => 'deleted_' . $user->id . '@example.test',
      'email_verified_at' => null,
      'password' => bcrypt(Str::random(32)), // Securely randomize password
      'remember_token' => null,
    ]);

    // Anonymize user details
    if ($user->detail) {
      $user->detail()->update([
        'first_name' => 'Deleted',
        'middle_name' => null,
        'last_name' => 'User',
        'dob' => null,
        'phone' => null,
        'avatar' => null,
        'emergency_c_name' => null,
        'emergency_c_phone' => null,
      ]);
    }

    // kill session
    Auth::guard('web')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return $this->successResponse([
      'message' => 'Account deleted successfully.',
    ]);
  }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class UserInfoService
{
  // Currently only used in the client for updating user profile on Edit Profile
  public function updateUserProfile(User $user, array $validated)
  {
    try {
      DB::beginTransaction();

      // Update user detail
      $user->detail()->updateOrCreate(
        ['user_id' => $user->id],
        [
          'language' => $validated['language'],
          'phone' => $validated['phoneNumber'],
          'emergency_c_name' => $validated['emergencyContactName'],
          'emergency_c_phone' => $validated['emergencyPhoneNumber'],
        ]
      );

      // Update address
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

      // Update passenger data for existing bookings ( NEW or ON HOLD)
      $bookings = $user->bookings()->whereIn('status', ['NEW', 'ON HOLD'])->get();

      foreach ($bookings as $booking) {
        foreach ($booking->passengers->where('survivor_number', $user->survivorNumber->survivor_number) as $passenger) {
          $passenger->update([
            'language' => $validated['language'],
            'phone' => $validated['phoneNumber'],
            'emergency_c_name' => $validated['emergencyContactName'],
            'emergency_c_phone' => $validated['emergencyPhoneNumber'],
            'country' => $validated['country'],
            'state' => $validated['state'],
            'address_first' => $validated['addressLine1'],
            'address_second' => $validated['addressLine2'],
            'city' => $validated['city'],
            'postal_code' => $validated['zipCode'],
            'special_request' => $validated['specialRequest'] ?? null,
            'special_options' => $validated['specialOptions'] ?? null,
          ]);
        }
      }

      DB::commit();

      return [
        'success' => true,
        'message' => 'Profile updated successfully.',
      ];
    } catch (Exception $e) {
      DB::rollBack();
      \Log::error('Failed to update user profile: ' . $e->getMessage());

      return [
        'success' => false,
        'message' => 'An error occurred while updating the profile.',
      ];
    }
  }
}

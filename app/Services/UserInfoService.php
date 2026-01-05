<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class UserInfoService {
    // Currently only used in the client for updating user profile on Edit Profile
    public function updateUserProfile(User $user, array $validated) {
        try {
            DB::beginTransaction();

            // Update user detail
            $user->detail()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'language' => $validated['language'],
                    'phone' => $validated['phone_number'],
                    'emergency_c_name' => $validated['emergency_contact_name'],
                    'emergency_c_phone' => $validated['emergency_phone_number'],
                ],
            );

            // Update address
            $user->customerAddress()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'country' => $validated['country'],
                    'state' => $validated['state'],
                    'address_first' => $validated['address_line_1'],
                    'address_second' => $validated['address_line_2'],
                    'city' => $validated['city'],
                    'postal_code' => $validated['zip_code'],
                ],
            );

            // Update passenger data for existing bookings ( NEW or ON HOLD)
            $bookings = $user
                ->bookings()
                ->whereIn('status', ['NEW', 'ON HOLD'])
                ->get();

            foreach ($bookings as $booking) {
                foreach (
                    $booking->passengers->where('survivor_number', $user->survivorNumber->survivor_number)
                    as $passenger
                ) {
                    $passenger->update([
                        'language' => $validated['language'],
                        'phone' => $validated['phone_number'],
                        'emergency_c_name' => $validated['emergency_contact_name'],
                        'emergency_c_phone' => $validated['emergency_phone_number'],
                        'country' => $validated['country'],
                        'state' => $validated['state'],
                        'address_first' => $validated['address_line_1'],
                        'address_second' => $validated['address_line_2'],
                        'city' => $validated['city'],
                        'postal_code' => $validated['zip_code'],
                        'special_request' => $validated['special_request'] ?? null,
                        'special_options' => $validated['special_options'] ?? null,
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

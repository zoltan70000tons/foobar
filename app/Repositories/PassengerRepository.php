<?php

namespace App\Repositories;

use App\Interfaces\PassengerInterface;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PassengerRepository implements PassengerInterface
{
  public function create($data, Booking $booking): Passenger|bool
  {
    try {
      // Get authenticated user
      $user = Auth::user();
      if (!$user) {
        return false;
      }
      \Log::info("User: " . $user->id);

      $userDetails = $user->detail;

      \Log::info("User Details: " . $userDetails);

      $passengerData = [
        "booking_id" => $booking->id,
        "confirmed_booking_email" => false,
        "lead_passenger" => $data["lead_passenger"],
        "survivor_number" => $user->survivorNumber->survivor_number,
        "gender" => $userDetails->gender,
        "first_name" => $userDetails->first_name,
        "middle_name" => $userDetails->middle_name,
        "last_name" => $userDetails->last_name,
        "dob" => $userDetails->dob,
        "citizenship" => $userDetails->citizenship,
        "payment_method" => "CREDIT_CARD",
        "address_first" => $data["address_first"],
        "address_second" => $data["address_second"],
        "city" => $data["city"],
        "state" => $data["state"],
        "postal_code" => $data["postal_code"],
        "country" => $data["country"],
        "email" => $data["email"],
        "phone" => $data["phone"],
        "emergency_c_name" => $data["emergency_c_name"],
        "emergency_c_phone" => $data["emergency_c_phone"],
        "special_request" => $data["special_request"] ?? null,
        "newsletter" => $data["newsletter"],
        "travel_info" => false,
        "terms_n_cons" => $data["terms_n_cons"],
        "cabin_conf_accp" => false,
        "single_t_agreement" => false,
        "passenger_allocated_cost" => $data["passenger_allocated_cost"],
        "passenger_balance" => 0,
        "was_on_board" => false,
      ];
      $leadPassenger = Passenger::create($passengerData);
      $seats = $booking->cabin->cabinCategory->capacity -1;
      if ($seats > 0) {
        $result = $this->fillAditionalSeats($seats, $booking->id);
        if (!$result) {
          throw new \Exception("Error creating seats.");
        }
      }

      \Log::info("Passenger Data: " . json_encode($passengerData));
      return $leadPassenger;
    } catch (\Exception $e) {
      \Log::info("PassengerRepository@create: " . $e->getMessage());
      return false;
    }
  }


  private function fillAditionalSeats($seats,$bookingId): bool
  {
    try {
      for ($i = 0; $i < $seats; $i++) {
        $additionalPassengerData = [
          "booking_id" => $bookingId,
          "confirmed_booking_email" => false,
          "lead_passenger" => false,
          "survivor_number" => null,
          "gender" => null,
          "first_name" => 'Unknown',
          "middle_name" => 'Unknown',
          "last_name" => 'Unknown',
          "dob" => null,
          "citizenship" => null,
          "payment_method" => "CREDIT_CARD",
          "address_first" => 'Unknown',
          "address_second" => 'Unknown',
          "city" => null,
          "state" => null,
          "postal_code" => null,
          "country" => null,
          "email" => 'Unknown',
          "phone" => null,
          "emergency_c_name" => 'Unknown',
          "emergency_c_phone" => null,
          "special_request" => null,
          "newsletter" => false,
          "travel_info" => false,
          "terms_n_cons" => false,
          "cabin_conf_accp" => false,
          "single_t_agreement" => false,
          "passenger_allocated_cost" => 0,
          "passenger_balance" => 0,
          "was_on_board" => false,
        ];
        \Log::info("Passenger Data (Additional): " . json_encode($additionalPassengerData));
        Passenger::create($additionalPassengerData);
      }
      return true;
    } catch (\Exception $e) {
      \Log::error("Error filling additional seats: " . $e->getMessage());
      return false;
    }
  }
}

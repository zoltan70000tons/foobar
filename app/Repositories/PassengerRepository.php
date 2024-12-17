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
    \Log::info("PassengerRepository::create", ["data" => $data]);

    try {
      // Get authenticated user
      $user = Auth::user();
      if (!$user) {
        return false;
      }

      $userDetails = $user->detail;

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
        "payment_method" => $data["payment_method"],
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
        "cabin_conf_accp" => $data["cabin_conf_accp"],
        "single_t_agreement" => $data["single_t_agreement"],
        "passenger_allocated_cost" => $data["passenger_allocated_cost"],
        "passenger_balance" => 0,
        "was_on_board" => false,
      ];

      return Passenger::create($passengerData);
    } catch (\Exception $e) {
      dd($e->getMessage());

      return false;
    }
  }
}

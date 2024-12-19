<?php

namespace App\Repositories;

use App\Interfaces\PassengerInterface;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Log;

class PassengerRepository implements PassengerInterface
{
  public function create($data, Booking $booking): Passenger|bool
  {
    Log::info("PassengerRepository::create", ["data" => $data]);

    try {
      // Get authenticated user
      $user = Auth::user();
      if (!$user) {
        Log::info('No user');
        return false;
      }
      \Log::info("User: " . $user->id);

      $userDetails = $user->detail;

      \Log::info("User Details: " . $userDetails);
      
      $cabin = $booking->cabin;
      $cabinType = $cabin->cabinType->id;
      Log::info('cabin type = '. $cabinType);


      $passengerData = [ 
        "booking_id" => $booking->id,
        "confirmed_booking_email" => false,
        "lead_passenger" => true,
        "survivor_number" => $user->survivorNumber->survivor_number,
        "gender" => $userDetails->gender ?? null,
        "first_name" => $userDetails->first_name ?? null,
        "middle_name" => $userDetails->middle_name ?? null,
        "last_name" => $userDetails->last_name ?? null,
        "dob" => $userDetails->dob ?? null,
        "citizenship" => $userDetails->citizenship ?? null,
        "payment_method" => $data["payment_method"] ?? 'CREDIT_CARD',
        "address_first" => $data["address_first"] ?? null,
        "address_second" => $data["address_second"] ?? null,
        "city" => $data["city"] ?? null,
        "state" => $data["state"] ?? null,
        "postal_code" => $data["postal_code"] ?? null,
        "country" => $data["country"] ?? null,
        "email" => $data["email"] ?? null,
        "phone" => $data["phone"] ?? null,
        "emergency_c_name" => $data["emergency_c_name"] ?? null,
        "emergency_c_phone" => $data["emergency_c_phone"] ?? null,
        "special_request" => $data["special_request"] ?? null,
        "newsletter" => $data["newsletter"] ?? null,
        "travel_info" => false,
        "terms_n_cons" => $data["terms_n_cons"] ?? null,
        "cabin_conf_accp" => $data["cabin_conf_accp"] ?? null,
        "single_t_agreement" => $data["single_t_agreement"] ?? null,
        "passenger_allocated_cost" => $data["passenger_allocated_cost"] ?? null,
        "passenger_balance" => 0,
        "was_on_board" => false,
    ];
    
      $leadPassenger = Passenger::create($passengerData);
      $availableSeats = $booking->cabin->category->capacity -1;
      if($cabinType == 2 || $cabinType == 3){
      $availableSeats = 0;
      }
      
      if ($availableSeats > 0) {
        $result = $this->fillAditionalSeats($availableSeats, $booking->id);
        if (!$result) {
          throw new \Exception("Error creating seats.");
        }
      }

      Log::info("Passenger Data: " . json_encode($passengerData));
      return $leadPassenger;
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      Log::info("PassengerRepository@create: " . $e->getMessage());
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
          "first_name" => null,
          "middle_name" => null,
          "last_name" => null,
          "dob" => null,
          "citizenship" => null,
          "payment_method" => "CREDIT_CARD",
          "address_first" => null,
          "address_second" => null,
          "city" => null,
          "state" => null,
          "postal_code" => null,
          "country" => null,
          "email" => null,
          "phone" => null,
          "emergency_c_name" => null,
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
      Log::error("Error filling additional seats: " . $e->getMessage());
      return false;
    }
  }



  public function updateSeat(Passenger $passenger, Booking $booking, array $data){
    $passenger->update($data);
  }
}

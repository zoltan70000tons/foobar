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

            $userDetails = $user->detail;
            $leadPassenger = isset($data['cart']) ? $data['cart']['cabin_type'] === "private-cabin" : $data['lead_passenger'];
            $passengerAllocatedCost = isset($data['cart']) ? $data['cart']['price_total'] : $data['price'];
            $phone = isset($data['cart']) ? $data['phone']['number'] : $data['phone'];
            $emergencyCPhone = isset($data['cart']) ? $data['emergencyContactPhone']['number'] : $data['emergencyContactPhone'];
            $passengerData = [
                "booking_id" => $booking->id,
                "confirmed_booking_email" => false,
                "lead_passenger" => $leadPassenger,
                "survivor_number" => $user->survivorNumber->survivor_number,
                "gender" => $userDetails->gender,
                "first_name" => $userDetails->first_name,
                "middle_name" => $userDetails->middle_name,
                "last_name" => $userDetails->last_name,
                "dob" => $userDetails->dob,
                "citizenship" => $userDetails->citizenship,
                "payment_method" => "CREDIT_CARD",
                "address_first" => $data["addressLine1"],
                "address_second" => $data["addressLine2"],
                "city" => $data["city"],
                "state" => $data["state"],
                "postal_code" => $data["zipCode"],
                "country" => $data["country"],
                "email" => $data["email"],
                "phone" => $phone,
                "emergency_c_name" => $data["emergencyContactName"],
                "emergency_c_phone" => $emergencyCPhone,
                "special_request" => $data["specialRequest"] ?? null,
                "newsletter" => $data["newsletter"],
                "travel_info" => false,
                "terms_n_cons" => $data["terms"],
                "cabin_conf_accp" => false,
                "single_t_agreement" => false,
                "passenger_allocated_cost" => $passengerAllocatedCost,
                "passenger_balance" => 0,
                "was_on_board" => false
            ];
            return Passenger::create($passengerData);
        } catch (\Exception $e) {
            return false;
        }
    }
}

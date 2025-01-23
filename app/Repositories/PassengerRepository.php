<?php

namespace App\Repositories;

use App\Interfaces\PassengerInterface;
use App\Models\Booking;
use App\Models\Passenger;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Log;

class PassengerRepository implements PassengerInterface
{
  protected PaymentService $paymentService;

  public function __construct(
    PaymentService $paymentService
  ) {
    $this->paymentService = $paymentService;
  }
  public function create($data, Booking $booking): Passenger|bool
  {
    Log::info('PassengerRepository::create', ['data' => $data]);

    try {
      // Get authenticated user
      $user = Auth::user();
      if (!$user) {
        Log::info('No user');
        return false;
      }
      \Log::info('User: ' . $user->id);

      $userDetails = $user->detail;

      \Log::info('User Details: ' . $userDetails);

      $cabin = $booking->cabin;
      $cabinType = $cabin->cabinType->id;
      Log::info('cabin type = ' . $cabinType);

      $allocatedCost = $data['passenger_allocated_cost'] ?? 0;

      $passengerData = [
        'booking_id' => $booking->id,
        'confirmed_booking_email' => false,
        'lead_passenger' => true,
        'survivor_number' => $user->survivorNumber->survivor_number ?? null,
        'gender' => $userDetails->gender ?? null,
        'first_name' => $userDetails->first_name ?? null,
        'middle_name' => $userDetails->middle_name ?? null,
        'last_name' => $userDetails->last_name ?? null,
        'dob' => $userDetails->dob ?? null,
        'citizenship' => $userDetails->citizenship ?? null,
        'payment_method' => $data['payment_method'] ?? 'CREDIT_CARD',
        'address_first' => $data['address_first'] ?? null,
        'address_second' => $data['address_second'] ?? null,
        'city' => $data['city'] ?? null,
        'state' => $data['state'] ?? null,
        'postal_code' => $data['postal_code'] ?? null,
        'country' => $data['country'] ?? null,
        'email' => $data['email'] ?? null,
        'phone' => $data['phone'] ?? null,
        'emergency_c_name' => $data['emergency_c_name'] ?? null,
        'emergency_c_phone' => $data['emergency_c_phone'] ?? null,
        'special_request' => $data['special_request'] ?? null,
        'special_options' => $data['special_options'] ?? null,
        'newsletter' => $data['newsletter'] ?? null,
        'travel_info' => $data['travel_info'] ?? null,
        'hear_about' => $data['hear_about'] ?? null,
       // 'referral_details' => $data['referral_details'] ?? null,
        'terms_n_cons' => $data['terms_n_cons'] ?? null,
        'cabin_conf_accp' => $data['cabin_conf_accp'] ?? null,
        'single_t_agreement' => $data['single_t_agreement'] ?? null,
        'passenger_allocated_cost' => $allocatedCost,
        'passenger_balance' => 0,
        'was_on_board' => false,
      ];

      $leadPassenger = Passenger::create($passengerData);
      $availableSeats = $booking->cabin->category->capacity - 1;
      if ($cabinType == 2 || $cabinType == 3) {
        $availableSeats = 0;
      }

      if ($availableSeats > 0) {
        $installments = false;
        if (is_numeric($data['number_of_installments']) && $data['number_of_installments'] > 1) {
          //here we have to enable installments
          $installments = $data['number_of_installments'];
          // $this->paymentService->createInstallments($passId, $installments);
        }
        $result = $this->fillAditionalSeats($availableSeats, $booking->id, $allocatedCost, $installments);
        if (!$result) {
          throw new \Exception('Error creating seats.');
        }
      }

      Log::info('Passenger Data: ' . json_encode($passengerData));
      return $leadPassenger;
    } catch (\Exception $e) {
      dd($e->getMessage());
      Log::error($e->getMessage());
      Log::info('PassengerRepository@create: ' . $e->getMessage());
      return false;
    }
  }

   public function find(int $event_id, $passenger_id, $booking_id): bool|Passenger
{
    try {
      // Query the Passenger model and ensure conditions are met
      $passenger = Passenger::where('id', $passenger_id)
        ->whereHas('booking', function ($query) use ($event_id, $booking_id) {
          $query->where('id', $booking_id)
            ->where('event_id', $event_id);
        })
        ->first();
      return $passenger ?: false;
    } catch (\Exception $e) {
      Log::error("Error finding passenger: {$e->getMessage()}");
      return false;
    }
   }

  private function fillAditionalSeats($seats, $bookingId, $allocatedCost, $installments = false): bool
  {
    try {
      for ($i = 0; $i < $seats; $i++) {
        $additionalPassengerData = [
          'booking_id' => $bookingId,
          'confirmed_booking_email' => false,
          'lead_passenger' => false,
          'survivor_number' => null,
          'gender' => null,
          'first_name' => null,
          'middle_name' => null,
          'last_name' => null,
          'dob' => null,
          'citizenship' => null,
          'payment_method' => 'CREDIT_CARD',
          'address_first' => null,
          'address_second' => null,
          'city' => null,
          'state' => null,
          'postal_code' => null,
          'country' => null,
          'email' => null,
          'phone' => null,
          'emergency_c_name' => null,
          'emergency_c_phone' => null,
          'special_request' => null,
          'newsletter' => false,
          'travel_info' => false,
          'terms_n_cons' => false,
          'cabin_conf_accp' => false,
          'single_t_agreement' => false,
          'passenger_allocated_cost' => $allocatedCost,
          'passenger_balance' => 0,
          'was_on_board' => false,
        ];
        Log::info('Passenger Data (Additional): ' . json_encode($additionalPassengerData));
        $seat = Passenger::create($additionalPassengerData);
        if (is_numeric($installments) && $installments > 1) {
          $this->paymentService->createInstallments($seat->id, $installments);
        }
      }
      return true;
    } catch (\Exception $e) {
      dd($e->getMessage());
      Log::error('Error filling additional seats: ' . $e->getMessage());
      return false;
    }
  }

  public function updateSeat(Passenger $passenger, Booking $booking, array $data)
  {
    $passenger->update($data);
  }
}

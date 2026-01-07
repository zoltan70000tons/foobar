<?php

namespace App\Repositories;

use App\Interfaces\PassengerInterface;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\User;
use App\Models\UserLog;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Log;

class PassengerRepository implements PassengerInterface {
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService) {
        $this->paymentService = $paymentService;
    }

    /**
     * Create lead passenger and additional seats for a booking
     *
     * @param array $data Passenger data
     * @param Booking $booking The booking instance
     * @return Passenger[] Array of created Passenger models
     */
    public function create(array $data, Booking $booking): array {
        Log::info('PassengerRepository::create', ['data' => $data]);

        // Auth is mandatory – repository cannot proceed without it
        $user = Auth::user();
        if (!$user) {
            throw new \RuntimeException('Authenticated user not found.');
        }

        $userDetails = $user->detail;
        $cabin = $booking->cabin;
        $cabinType = $cabin->cabinType->id;

        $allocatedCost = $data['passenger_allocated_cost'] ?? 0;
        $paymentMethod = $data['payment_method'] ?? 'CREDIT_CARD';

        $passengerData = [
            'booking_id' => $booking->id,
            'confirmed_booking_email' => false,
            'lead_passenger' => $data['lead_passenger'] ?? false,
            'survivor_number' => $data['survivor_number'] ?? ($user->survivorNumber->survivor_number ?? null),
            'gender' => $data['gender'] ?? ($userDetails->gender ?? null),
            'first_name' => $data['first_name'] ?? ($userDetails->first_name ?? null),
            'middle_name' => $data['middle_name'] ?? ($userDetails->middle_name ?? null),
            'last_name' => $data['last_name'] ?? ($userDetails->last_name ?? null),
            'dob' => $data['dob'] ?? ($userDetails->dob ?? null),
            'citizenship' => $data['citizenship'] ?? ($userDetails->citizenship ?? null),
            'payment_method' => $paymentMethod,
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
            'dietary_preferences' => $data['dietary_preferences'] ?? null,
            'newsletter' => $data['newsletter'] ?? false,
            'travel_info' => $data['travel_info'] ?? null,
            'hear_about' => $data['hear_about'] ?? null,
            'terms_n_cons' => $data['terms_n_cons'] ?? null,
            'cabin_conf_accp' => $data['cabin_conf_accp'] ?? null,
            'single_t_agreement' => $data['single_t_agreement'] ?? null,
            'passenger_allocated_cost' => $allocatedCost,
            'passenger_order' => 1,
            'passenger_balance' => 0,
            'was_on_board' => false,
        ];

        $leadPassenger = Passenger::create($passengerData);

        if (!$leadPassenger) {
            throw new \RuntimeException('Failed to create lead passenger.');
        }

        $availableSeats = $booking->cabin->category->capacity - 1;
        if ($cabinType === 2 || $cabinType === 3) {
            $availableSeats = 0;
        }

        // Define number of installments for all passengers in the booking
        $installments = $data['number_of_installments'];

        // Create installments for lead passenger
        $this->paymentService->createInstallments($leadPassenger->id, $installments);

        $allPassengers = [$leadPassenger];

        if ($availableSeats > 0) {
            $additionalPassengers = $this->fillAditionalSeats(
                $availableSeats,
                $booking->id,
                $allocatedCost,
                $paymentMethod,
                $installments,
            );

            if (!is_array($additionalPassengers)) {
                throw new \RuntimeException('Failed to create additional passengers.');
            }

            $allPassengers = array_merge($allPassengers, $additionalPassengers);
        }

        return $allPassengers;
    }

    public function find(int $event_id, $passenger_id, $booking_id): bool|Passenger {
        try {
            // Query the Passenger model and ensure conditions are met
            $passenger = Passenger::where('id', $passenger_id)
                ->whereHas('booking', function ($query) use ($event_id, $booking_id) {
                    $query->where('id', $booking_id)->where('event_id', $event_id);
                })
                ->first();
            return $passenger ?: false;
        } catch (\Exception $e) {
            Log::error("Error finding passenger: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Fill additional seats for a booking
     *
     * @param int $seats Number of additional seats to create
     * @param int $bookingId The booking ID
     * @param float $allocatedCost Cost allocated per passenger
     * @param string $paymentMethod Payment method
     * @param int|false $installments Number of installments
     * @return Passenger[]|false Array of created Passenger models or false on failure
     */
    public function fillAditionalSeats($seats, $bookingId, $allocatedCost, $paymentMethod, $installments): array|bool {
        try {
            $currentMaxOrder = Passenger::where('booking_id', $bookingId)->max('passenger_order') ?? 1;
            $createdPassengers = [];

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
                    'payment_method' => $paymentMethod,
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
                    'passenger_order' => $currentMaxOrder + $i + 1,
                    'passenger_balance' => 0,
                    'was_on_board' => false,
                    //'language' => 'en',
                ];
                Log::info('Passenger Data (Additional): ' . json_encode($additionalPassengerData));
                $seat = Passenger::create($additionalPassengerData);

                $this->paymentService->createInstallments($seat->id, $installments);
                $createdPassengers[] = $seat;
            }
            return $createdPassengers;
        } catch (\Exception $e) {
            Log::error('Error filling additional seats: ' . $e->getMessage());
            return false;
        }
    }

    public function updateSeat(Passenger $passenger, Booking $booking, array $data) {
        $passenger->update($data);
    }
}

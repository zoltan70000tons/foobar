<?php

namespace App\Traits;

use Exception;

trait BookingHandler {
    /**
     * Create a new booking using the booking repository.
     *
     * @param array $bookingData The data for the booking (e.g., customer_id, payment_method, etc.)
     * @param array $passengerData The data for the passenger (e.g., name, phone, etc.)
     * @param \App\Models\Cabin $cabin The cabin to associate with the booking
     * @param array $reservationId Reservation id on temporary table.)
     * @return mixed The result of the booking creation process (success or error)
     */
    public function createBooking(array $bookingData, $passengerData, $cabin = null, $reservationId) {
        try {
            // Validate required fields
            if (empty($data)) {
                throw new Exception('Booking data is required.');
            }

            if (!$cabin) {
                throw new Exception('Valid cabin is required.');
            }

            // Call the repository to create the booking
            $result = $this->bookingRepository->createBooking($bookingData, $passengerData, $cabin, $reservationId);

            // Return the result from the repository
            return $result;
        } catch (Exception $e) {
            // Handle errors gracefully and return an error array
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }
}

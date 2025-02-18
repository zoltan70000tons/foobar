<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Cabin;

class CustomerBookingRepository
{
  protected $booking;
  protected $passenger;
  protected $cabin;

  public function __construct(Booking $booking, Passenger $passenger, Cabin $cabin)
  {
    $this->booking = $booking;
    $this->passenger = $passenger;
    $this->cabin = $cabin;
  }

  /**
   * Get booking by booking code
   * - with passengers ( depends on the booking type )
   * - with cabin
   *
   * @param string $bookingCode
   * @return Booking
   */
  public function getBookingByCode($bookingCode, $user = null)
  {
    $user_survivor_number = $user->survivor_number ?? null;

    $booking = Booking::with(
      'passengers.fees',
      'passengers.installments',
      'passengers.passengerInvitation',
      'cabin.category',
      'cabin.cabinType',
      'event'
    )
      ->where('booking_code', $bookingCode)
      ->first();

    // if booking is_single_occupancy then do not return other passengers
    if ($booking->is_single_occupancy) {
      $booking->passengers = $booking->passengers->filter(function ($passenger) use ($user_survivor_number) {
        return $passenger->survivor_number === $user_survivor_number;
      });
    }

    // if booking payment_plan is INSTALLMENTS get all installments where passenger is lead_passenger
    if ($booking->payment_plan === 'INSTALLMENTS') {
      $booking->passengers->map(function ($passenger) {
        $passenger->installments = $passenger->installments;
      });
    }

    return $booking;
  }

  /**
   * Get all bookings related to the user
   * - with passengers ( depends on the booking type )
   * - with cabin
   *
   * @return Booking
   */
  public function getAllBookings($user)
  {
    $user_survivor_number = $user->survivor_number ?? null;
    $customer_id = $user->id ?? null;

    $bookings = Booking::with('passengers', 'cabin.category', 'cabin.cabinType', 'event')
      ->where('customer_id', $customer_id)
      ->get();

    // if booking is_single_occupancy then do not return other passengers
    $bookings->map(function ($booking) use ($user_survivor_number) {
      if ($booking->is_single_occupancy) {
        $booking->passengers = $booking->passengers->filter(function ($passenger) use ($user_survivor_number) {
          return $passenger->survivor_number === $user_survivor_number;
        });
      }
    });

    return $bookings;
  }

  // create passenger
  public function createPassenger($data)
  {
    return Passenger::create($data);
  }

  /*
  |--------------------------------------------------------------------------
  | Add passenger manually
  |--------------------------------------------------------------------------
  |
  | This method will add a passenger to the booking manually
  |
  */
  public function addPassengerManually($bookingCode, $validated)
  {
    $booking = $this->getBookingByCode($bookingCode);

    // log params
    \Log::info('Add passenger manually: ' . json_encode($validated));

    //$cabinCapacity = $booking->cabin->category->capacity;
    $passengers = $booking->passengers;

    $emptyPassenger = $passengers
      ->filter(function ($passenger) {
        return $passenger->first_name === null &&
          $passenger->gender === null &&
          $passenger->dob === null &&
          $passenger->empty_seat === false;
      })
      ->first();

    if ($emptyPassenger) {
      try {
        $emptyPassenger->update([
          'booking_id' => $booking->id,
          'first_name' => $validated['firstName'],
          'middle_name' => $validated['middleName'] ?? null,
          'last_name' => $validated['lastName'],
          'dob' => $validated['dateOfBirth'],
          'gender' => $validated['gender'],
          'citizenship' => $validated['citizenship'],
          'address_first' => $validated['addressLine1'],
          'address_second' => $validated['addressLine2'],
          'city' => $validated['city'],
          'state' => $validated['state'],
          'postal_code' => $validated['zipCode'],
          'country' => $validated['country'],
          'email' => $validated['email'],
          'phone' => $validated['phoneNumber'],
          'emergency_c_name' => $validated['emergencyContactName'],
          'emergency_c_phone' => $validated['emergencyPhoneNumber'],
          'special_request' => $validated['specialRequest'],
        ]);
      } catch (\Exception $e) {
        \Log::error('Error while adding passenger: ' . $e->getMessage());
        return response()->json(['message' => 'Error while adding passenger'], 500);
      }

      return response()->json(['message' => 'Passenger added'], 200);
    }

    return response()->json(['message' => 'No empty seats available'], 404);
  }

  /*
  |--------------------------------------------------------------------------
  | Set empty seat
  |--------------------------------------------------------------------------
  |
  | This method will add an empty seat to the booking
  |
  */
  public function setEmptySeat($bookingCode)
  {
    $booking = $this->getBookingByCode($bookingCode);

    //$cabinCapacity = $booking->cabin->category->capacity;
    $passengers = $booking->passengers;

    $emptyPassenger = $passengers
      ->filter(function ($passenger) {
        return $passenger->first_name === null &&
          $passenger->gender === null &&
          $passenger->dob === null &&
          $passenger->empty_seat === false;
      })
      ->first();

    if ($emptyPassenger) {
      // add one null passenger to the same booking
      try {
        $emptyPassenger->update([
          'booking_id' => $booking->id,
          'empty_seat' => true,
        ]);
      } catch (\Exception $e) {
        return response()->json(['message' => 'Error while adding empty seat'], 500);
      }

      return response()->json(['message' => 'Empty seat added'], 200);
    }

    return response()->json(['message' => 'No empty seats available'], 404);
  }
}

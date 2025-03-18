<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Cabin;
use App\Models\PassengerInvitation;
use Illuminate\Support\Str;
use App\Models\User;
use Mockery\Generator\StringManipulation\Pass\Pass;

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
  public function getBookingByCode(int $eventId, string $bookingCode, $user = null)
  {
    $user_survivor_number = $user->survivorNumber->survivor_number ?? null;

    $booking = Booking::with(
      'adjustments',
      'passengers.fees',
      'passengers.installments',
      'passengers.passengerInvitation',
      'cabin.category',
      'cabin.cabinType',
      'passengers.payments',
      'event'
    )
      ->where('booking_code', $bookingCode)
      ->where('event_id', $eventId)
      ->first();

    // if booking is_single_occupancy then do not return other passengers
    if ($booking->is_single_occupancy) {
      $filteredPassengers = $booking->passengers->filter(function ($passenger) use ($user_survivor_number) {
        return $passenger->survivor_number === $user_survivor_number;
      });

      // Use the filtered passengers for returning, instead of modifying the model
      $booking->setRelation('passengers', $filteredPassengers);
    }

    if (!$booking->is_single_occupancy && $user_survivor_number) {
      // Get the passenger record for the current user
      $userPassenger = $booking->passengers->firstWhere('survivor_number', $user_survivor_number);

      // If the record exists and is not marked as lead_passenger, filter out others
      if ($userPassenger && !$userPassenger->lead_passenger) {
        $filteredPassengers = $booking->passengers
          ->filter(function ($passenger) use ($user_survivor_number) {
            return $passenger->survivor_number === $user_survivor_number;
          })
          ->values(); // re-index the collection
        // Set the filtered passengers relation to the booking
        $booking->setRelation('passengers', $filteredPassengers);
      }
    }

    // if booking payment_plan is INSTALLMENTS get all installments where passenger is lead_passenger
    if ($booking->payment_plan === 'INSTALLMENTS') {
      $booking->passengers->each(function ($passenger) {
        $passenger->setRelation('installments', $passenger->installments);
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
    $user_survivor_number = $user->survivorNumber->survivor_number ?? null;
    //$customer_id = $user->id ?? null;

    // $bookings = Booking::with('passengers', 'cabin.category', 'cabin.cabinType', 'event')
    //   ->where('customer_id', $customer_id)
    //   ->get();

    $bookings = Passenger::with('booking', 'booking.cabin.category', 'booking.cabin.cabinType', 'booking.event')
      ->where('survivor_number', $user_survivor_number)
      ->get()
      ->map(function ($passenger) {
        return $passenger->booking;
      })
      ->unique('id');

    // if booking is_single_occupancy then do not return other passengers
    $bookings->map(function ($booking) use ($user_survivor_number) {
      if ($booking->is_single_occupancy) {
        $filteredPassengers = $booking->passengers->filter(function ($passenger) use ($user_survivor_number) {
          return $passenger->survivor_number === $user_survivor_number;
        });
        $booking->setRelation('passengers', $filteredPassengers);
      }
    });

    // unset agent_id
    $bookings->map(function ($booking) {
      unset($booking->agent_id);
      if ($booking->status === 'NEW') {
        unset($booking->booking_code);
        unset($booking->cabin->cabin_number);
        unset($booking->cabin->cabinSpec->cabin_number);
        unset($booking->cabin->cabinSpec->id);
        unset($booking->cabin->cabin_spec_id);
        unset($booking->cabin->id);
        unset($booking->cabin_id);
      }
    });

    //$res = $bookings->toArray();

    return $bookings;
  }

  // create passenger
  public function createPassenger($data)
  {
    return Passenger::create($data);
  }

  /*
  |--------------------------------------------------------------------------
  | Add passenger with token
  |--------------------------------------------------------------------------
  | 
  | This method will add a passenger with token, for non auth and auth users invitation
  |
  */
  public function addPassengerWithToken(int $eventId, string $bookingCode, string $token, array $dataToUpdate)
  {
    if (!$token || !$bookingCode || !$eventId) {
      return response()->json(['message' => 'Invalid token'], 400);
    }

    $booking = $this->getBookingByCode($eventId, $bookingCode);

    $passengerSlotId = PassengerInvitation::where('booking_id', $booking->id)
      ->where('token', $token)
      ->first();

    if (!$passengerSlotId) {
      return response()->json(['message' => 'Invalid token'], 400);
    }

    // check the bookingCode === booking_code
    if ($booking->booking_code !== $bookingCode) {
      return response()->json(['message' => 'Invalid booking code'], 400);
    }

    $passEmail = $dataToUpdate['email'];

    // if passenger for this booking with this email exist, return error
    $passenger = Passenger::where('booking_id', $booking->id)
      ->where('email', $passEmail)
      ->first();

    if ($passenger) {
      return response()->json(['message' => 'Passenger with this email already exists'], 400);
    }

    $emptyPassenger = Passenger::where('id', $passengerSlotId->passenger_id)->first();

    // update passenger id and remove PassengerInvitation
    if ($emptyPassenger) {
      $dataToUpdate['booking_id'] = $booking->id;

      try {
        $emptyPassenger->update($dataToUpdate);

        $passengerSlotId->delete();
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
  | Add passenger manually
  |--------------------------------------------------------------------------
  |
  | This method will add a passenger to the booking manually
  |
  */
  public function addPassengerManually(int $eventId, string $bookingCode, $passenger, $validated)
  {
    $booking = $this->getBookingByCode($eventId, $bookingCode);

    // log params
    \Log::info('Add passenger manually: ' . json_encode($validated));

    //$cabinCapacity = $booking->cabin->category->capacity;
    // $passengers = $booking->passengers;

    // $emptyPassenger = $passengers
    //   ->filter(function ($passenger) {
    //     return $passenger->first_name === null &&
    //       $passenger->gender === null &&
    //       $passenger->dob === null &&
    //       $passenger->empty_seat === false;
    //   })
    //   ->first();

    if ($passenger) {
      try {
        $passenger->update([
          'survivor_number' => $validated['survivorNumber'] ?? null,
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
          'language' => $validated['language'] ?? 'en',
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
  public function setEmptySeat(int $eventId, string $bookingCode, $passengerOrder)
  {
    $booking = $this->getBookingByCode($eventId, $bookingCode);

    //$cabinCapacity = $booking->cabin->category->capacity;
    $passengers = $booking->passengers;

    // get passenger based on passenger order
    $emptyPassenger = $passengers
      ->filter(function ($passenger) use ($passengerOrder) {
        return $passenger->passenger_order === $passengerOrder;
      })
      ->first();

    // $emptyPassenger = $passengers
    //   ->filter(function ($passenger) {
    //     return $passenger->first_name === null &&
    //       $passenger->gender === null &&
    //       $passenger->dob === null &&
    //       $passenger->empty_seat === false;
    //   })
    //   ->first();

    $passengerSlotId = PassengerInvitation::where('booking_id', $booking->id)
      ->where('passenger_id', $emptyPassenger->id)
      ->first();

    if ($passengerSlotId) {
      return response()->json(['message' => 'Passenger with this slot has already been taken'], 400);
    }

    if (
      $emptyPassenger &&
      $emptyPassenger->first_name === null &&
      $emptyPassenger->dob === null &&
      $emptyPassenger->empty_seat === false
    ) {
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

  /*
  |--------------------------------------------------------------------------
  | Create passenger invitation
  |--------------------------------------------------------------------------
  | 
  | This method will create a passenger invitation
  |
  */
  public function createPassengerInvitation($passenger, $booking, $email)
  {
    $token = Str::random(32);

    $invitation = PassengerInvitation::create([
      'passenger_id' => $passenger->id,
      'booking_id' => $booking->id,
      'token' => $token,
      'email' => $email,
      'sent_at' => now(),
    ]);

    return $invitation;
  }
}

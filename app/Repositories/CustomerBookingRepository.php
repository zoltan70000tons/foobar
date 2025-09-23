<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Cabin;
use App\Models\PassengerInvitation;
use Illuminate\Support\Str;
use App\Traits\StringNormalization;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerResetSeat;
use App\Models\User;
use Mockery\Generator\StringManipulation\Pass\Pass;
use App\Services\EmailTemplateService;
use App\Notifications\NewAddPaxAddedToBooking;
use App\Notifications\LeadPassRemovesSomeone;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class CustomerBookingRepository
{
  use StringNormalization;

  protected $booking;
  protected $passenger;
  protected $cabin;
  protected $emailService;

  public function __construct(Booking $booking, Passenger $passenger, Cabin $cabin, EmailTemplateService $emailService)
  {
    $this->booking = $booking;
    $this->passenger = $passenger;
    $this->cabin = $cabin;
    $this->emailService = $emailService;
  }

  /**
   * Get booking by booking code
   * - with passengers ( depends on the booking type )
   * - with cabin
   *
   * @param string $bookingCode
   * @return Booking
   */
  public function getBooking(
    int $eventId,
    ?string $bookingCode,
    $user = null,
    ?string $requestId = null
  ) {
    // survivor number of the authenticated user (if any)
    $userSurvivorNumber = $user->survivorNumber->survivor_number ?? null;

    // Common eager-loads
    $with = [
      'adjustments',
      'cabin.category',
      'cabin.cabinType',
      'event',
      'passengers.fees' => function ($query) {
        $query->select('id', 'passenger_id', 'amount', 'type', 'created_at', 'updated_at');
      },
      'passengers.discounts' => function ($query) {
        $query->select('id', 'passenger_id', 'amount', 'type', 'operation', 'created_at', 'updated_at');
      },
      'passengers.installments',
      'passengers.passengerInvitation',
      'passengers.payments',
    ];


      $with[] = 'passengers.onboardCredits';
    

    // Conditional query based on bookingCode or requestId
    $booking = Booking::with($with)
      ->where('event_id', $eventId)
      ->when($requestId, function ($q) use ($requestId) {
        $q->where('booking_request_id', $requestId);
      }, function ($q) use ($bookingCode) {
        $q->where('booking_code', $bookingCode);
      })
      ->first();

    if (!$booking) {
      return null;
    }

    // Check for user-passenger information match
    if ($user) {
      $first = $user->detail->first_name ?? null;
      $last  = $user->detail->last_name ?? null;
      $dob   = $user->detail->dob ?? null;

      $userPassenger = $booking->passengers->first(function ($p) use ($first, $last, $dob) {
        return $p->first_name === $first
          && $p->last_name === $last
          && $p->dob === $dob;
      });

      if (!$userPassenger) {
        return null;
      }
    }

    // Append installment_status to each passenger
    $booking->passengers->each->append('installment_status');

    // Get all unfiltered passengers data
    $passengers = $booking->passengers;

    if ($booking->is_single_occupancy) {
      // Only show the current user's passenger record
      $passengers = $passengers->where('survivor_number', $userSurvivorNumber)->values();
    } elseif ($userSurvivorNumber) {
      // If the user is on the booking and NOT lead_passenger, only show their own record
      $current = $passengers->firstWhere('survivor_number', $userSurvivorNumber);
      if ($current && !$current->lead_passenger) {
        $passengers = $passengers->where('survivor_number', $userSurvivorNumber)->values();
      }
    }

    $booking->setRelation('passengers', $passengers);

    // Append installments to each passenger
    $booking->passengers->each(function ($p) {
      $p->setRelation('installments', $p->installments);
    });

    return $booking;
  }


  // get booking by booking code
  public function getBookingByCode(int $eventId, string $bookingCode, $user = null)
  {
    return $this->getBooking($eventId, $bookingCode, $user, null);
  }

  // get booking by request id
  public function getBookingByRequestId(int $eventId, string $requestId, $user = null) {
    //
    return $this->getBooking($eventId, null, $user, $requestId);
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

    // Validate survivor number for active bookings within the same event
    if (
      !empty($dataToUpdate['survivor_number']) &&
      Passenger::checkSurvivorInActiveBookings($dataToUpdate['survivor_number'], $eventId)
    ) {
      return response()->json(
        ['message' => 'A passenger with this Survivor Number already exists in an active booking for this event.'],
        409
      );
    }

    $emptyPassenger = Passenger::where('id', $passengerSlotId->passenger_id)->first();

    // update passenger id and remove PassengerInvitation
    if ($emptyPassenger) {
      $dataToUpdate['booking_id'] = $booking->id;
      $dataToUpdate['cabin_conf_accp'] = true;
      $dataToUpdate['terms_n_cons'] = true;

      try {
        $emptyPassenger->update($dataToUpdate);

        $passengerSlotId->delete();
      } catch (\Exception $e) {
        \Log::error('Error while adding passenger', [
          'error' => $e->getMessage(),
          'event_id' => $eventId,
          'booking_code' => $bookingCode,
        ]);
        return response()->json(['message' => 'Error while adding passenger'], 500);
      }

      $msg = $dataToUpdate['survivor_number']
        ? 'Passenger added with his own acocunt'
        : 'Passenger added via form without account';

      // send notification to slack
      try {
        Notification::route('slack', env('SLACK_BOOKING_ENGINE_NOTIFICATIONS'))->notify(
          new NewAddPaxAddedToBooking($bookingCode, $emptyPassenger->email, $msg, $booking)
        );
      } catch (\Exception $e) {
        // Optionally log the failure so you know something went wrong
        Log::warning('Slack notification failed: ' . $e->getMessage());
      }

      // reload passengers after update
      $emptyPassenger->refresh();
      $booking->load('passengers');

      // Send confirmation email
      $this->sendPassengerConfirmationEmail($booking);

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

    // Perform survivor number validation only if it exists
    if (
      !empty($validated['survivor_number']) &&
      Passenger::checkSurvivorInActiveBookings($validated['survivor_number'], $eventId)
    ) {
      return response()->json(
        ['message' => 'A passenger with this Survivor Number already exists in an active booking for this event.'],
        409
      );
    }

    if ($passenger) {
      try {
        $passenger->update([
          'survivor_number' => $validated['survivor_number'] ?? null,
          'booking_id' => $booking->id,
          'first_name' => $this->normalizeString($validated['first_name']),
          'middle_name' => $this->normalizeString($validated['middle_name']) ?? null,
          'last_name' => $this->normalizeString($validated['last_name']),
          'dob' => $validated['date_of_birth'],
          'gender' => $validated['gender'],
          'citizenship' => $validated['citizenship'],
          'address_first' => $validated['address_line_1'],
          'address_second' => $validated['address_line_2'],
          'city' => $validated['city'],
          'state' => $validated['state'],
          'postal_code' => $validated['zip_code'],
          'country' => $validated['country'],
          'email' => $validated['email'],
          'phone' => $validated['phone_number'],
          'emergency_c_name' => $this->normalizeString($validated['emergency_contact_name']),
          'emergency_c_phone' => $validated['emergency_phone_number'],
          'special_request' => $validated['special_request'],
          'language' => $validated['language'] ?? 'en',
          'terms_n_cons' => true,
          'cabin_conf_accp' => true,
        ]);
      } catch (\Exception $e) {
        \Log::error('Error while adding passenger', [
          'error' => $e->getMessage(),
          'event_id' => $eventId,
          'booking_code' => $bookingCode,
          'passenger_data' => $validated,
        ]);
        return response()->json(['message' => 'Error while adding passenger'], 500);
      }

      // send notification to slack
      try {
        Notification::route('slack', env('SLACK_BOOKING_ENGINE_NOTIFICATIONS'))->notify(
          new NewAddPaxAddedToBooking(
            $bookingCode,
            $passenger->email,
            'Passenger added manually by Lead Passenger',
            $booking
          )
        );
      } catch (\Exception $e) {
        // Optionally log the failure so you know something went wrong
        Log::warning('Slack notification failed: ' . $e->getMessage());
      }

      // reload passengers after update
      $passenger->refresh();
      $booking->load('passengers');

      // Send confirmation email
      $this->sendPassengerConfirmationEmail($booking);

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

    \Log::info('booking passenger: ' . json_encode($booking));


    //$cabinCapacity = $booking->cabin->category->capacity;
    $passengers = $booking->passengers;

    // get passenger based on passenger order
    $emptyPassenger = $passengers
      ->filter(function ($passenger) use ($passengerOrder) {
        return $passenger->passenger_order === $passengerOrder;
      })
      ->first();

    \Log::info('Empty passenger: ' . json_encode($emptyPassenger));

    $passengerSlotId = PassengerInvitation::where('booking_id', $booking->id)
      ->where('passenger_id', $emptyPassenger->id)
      ->first();

    if ($passengerSlotId) {
      return response()->json(['message' => 'Passenger with this slot has already been taken'], 400);
    }

    if (
      $emptyPassenger &&
      !$emptyPassenger->first_name &&
      !$emptyPassenger->dob &&
      $emptyPassenger->empty_seat === false
    ) {
      // add one null passenger to the same booking
      try {
        $emptyPassenger->update([
          'empty_seat' => true,
        ]);
      } catch (\Exception $e) {
        return response()->json(['message' => 'Error while adding empty seat'], 500);
      }
      ////// SEND EMAUI TO PASSENGER
      return response()->json(['message' => 'Empty seat added'], 200);
    }

    return response()->json(['message' => 'No empty seats available'], 404);
  }

  /*
  |--------------------------------------------------------------------------
  | Remove empty seat
  |--------------------------------------------------------------------------
  |
  | This method will remove an empty seat from the booking
  |
  */
  public function removeEmptySeat(int $eventId, string $bookingCode, $passengerOrder)
  {
    $booking = $this->getBookingByCode($eventId, $bookingCode);

    $passengers = $booking->passengers;

    // get passenger based on passenger order
    $emptyPassenger = $passengers
      ->filter(function ($passenger) use ($passengerOrder) {
        return $passenger->passenger_order === $passengerOrder;
      })
      ->first();

    if ($emptyPassenger && $emptyPassenger->empty_seat === true) {
      // remove one null passenger from the same booking
      try {
        $emptyPassenger->update([
          'empty_seat' => false,
        ]);
      } catch (\Exception $e) {
        return response()->json(['message' => 'Error while removing empty seat'], 500);
      }

      return response()->json(['message' => 'Empty seat removed'], 200);
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

  /*
  |--------------------------------------------------------------------------
  |  Reset passenger seat
  |--------------------------------------------------------------------------
  | 
  | Set default value for passenger seat
  |
  */
  public function resetPassengerSeat(int $eventId, string $bookingCode, $passengerOrder)
  {
    $booking = $this->getBookingByCode($eventId, $bookingCode);

    $passengers = $booking->passengers;

    $leadPassenger = $booking->passengers->where('lead_passenger', true)->first();

    // get passenger based on passenger order
    $passenger = $passengers
      ->filter(function ($passenger) use ($passengerOrder) {
        return $passenger->passenger_order === $passengerOrder;
      })
      ->first();

    $passengerEmail = $passenger->email ?? null;

    if ($passenger) {
      try {
        $passenger->update([
          'empty_seat' => false,
          'survivor_number' => null,
          'gender' => null,
          'first_name' => '',
          'middle_name' => '',
          'last_name' => '',
          'dob' => null,
          'citizenship' => null,
          'confirmed_booking_email' => false,
          'address_first' => null,
          'address_second' => null,
          'city' => null,
          'state' => null,
          'postal_code' => null,
          'country' => null,
          'phone' => null,
          'email' => null,
          'emergency_c_name' => null,
          'emergency_c_phone' => null,
          'special_request' => null,
          'special_options' => null,
          'hear_about' => null,
          'referal_details' => null,
          'newsletter' => false,
          'travel_info' => false,
          'terms_n_cons' => false,
          'empty_seat' => false,
          'cabin_conf_accp' => false,
          'single_t_agreement' => false,
          'language' => 'en',
        ]);
      } catch (\Exception $e) {
        return response()->json(['message' => 'Error while resetting passenger seat'], 500);
      }

      // send notification to slack
      try {
        Notification::route('slack', env('SLACK_BOOKING_ENGINE_NOTIFICATIONS'))->notify(
          new LeadPassRemovesSomeone($bookingCode, $leadPassenger->email, $passengerEmail, $booking)
        );
      } catch (\Exception $e) {
        // Optionally log the failure so you know something went wrong
        Log::warning('Slack notification failed: ' . $e->getMessage());
      }

      // send email to passenger, the invitation has been reset

      $this->sendEmailToPassenger($passengerEmail);

      return response()->json(['message' => 'Passenger seat reset'], 200);
    }

    return response()->json(['message' => 'Passenger not found'], 404);
  }

  // Send email to passenger, the invitation has been reset
  public function sendEmailToPassenger($passengerEmail)
  {
    $email = $passengerEmail ?? null;
    if (!$email) {
      return response()->json(['message' => 'Email not found'], 404);
    }

    Mail::to($email)->queue(new CustomerResetSeat($email));
  }

  /*
  |--------------------------------------------------------------------------
  | Send updated confirmation email to passengers
  |--------------------------------------------------------------------------
  |
  | This method will send an updated confirmation email to the passengers when a passenger is added
  |
  | @param Booking $booking
  | @param Passenger $passenger
  | @param string $templateCode
  | @return void
  */
  private function sendPassengerConfirmationEmail($booking, $templateCode = 'updated')
  {
    $leadPassenger = $booking->passengers->where('lead_passenger', true)->first();
    $leadPassengerLanguage = $leadPassenger?->language ?? 'en';
    $templateId = $this->emailService->getTemplateId($leadPassengerLanguage, $templateCode);

    if (!$templateId) {
      \Log::warning('Email template not found', [
        'template' => $templateCode,
        'language' => $leadPassengerLanguage,
        'booking_id' => $booking->id,
      ]);
      return;
    }

    $this->emailService->sendEmail($templateId, $booking, $leadPassenger, [], [], true, true);
  }
}

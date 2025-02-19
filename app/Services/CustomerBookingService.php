<?php

namespace App\Services;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use App\Repositories\CustomerBookingRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\PassengerInvitation;
use App\Mail\AddPassenger;

class CustomerBookingService
{
  protected $customerBookingRepository;

  public function __construct(CustomerBookingRepository $customerBookingRepository)
  {
    $this->customerBookingRepository = $customerBookingRepository;
  }

  /*
  |--------------------------------------------------------------------------
  | Get my bookings
  |--------------------------------------------------------------------------
  |
  |  In this method we check the type of booking and status of the booking
  |
  */
  public function getMyBookings($user)
  {
    $bookings = $this->customerBookingRepository->getAllBookings($user);

    // if booking have status new then do not return cabin id and number
    $bookings->map(function ($booking) {
      if ($booking->status === 'NEW' || $booking->status === 'CANCELLED') {
        $booking->cabin->makeHidden(['cabin_number']);
        $booking->cabin->cabinSpec->makeHidden(['cabin_number']);
      }
    });

    return $bookings;
  }

  /*
  |--------------------------------------------------------------------------
  | Get available seats
  |--------------------------------------------------------------------------
  |
  |  This method will return the number of available seats in the cabin
  |
  */
  public function getAvailableSeats($bookingCode)
  {
    // Fetch the booking data using the repository
    $booking = $this->customerBookingRepository->getBookingByCode($bookingCode);

    // Get the list of passengers associated with the booking
    $passengers = $booking->passengers;

    // Filter passengers where first_name, gender, and dob are null
    $emptySeats = $passengers->filter(function ($passenger) {
      return $passenger->first_name === null &&
        $passenger->gender === null &&
        $passenger->dob === null &&
        $passenger->empty_seat === false;
    });

    // Count the number of empty seats
    $availableSeats = $emptySeats->count();

    return $availableSeats;
  }

  /*
  |--------------------------------------------------------------------------
  | Add passenger via email
  |--------------------------------------------------------------------------
  |
  | This method will send an email to the user with a signed URL to add a passenger
  |
  */
  public function addPassengerViaEmail($booking, $passenger, $email)
  {
    $token = Str::random(32);

    $invitation = PassengerInvitation::create([
      'passenger_id' => $passenger->id,
      'booking_id' => $booking->id,
      'token' => $token,
      'email' => $email,
      'sent_at' => now(),
    ]);

    // generate signed url
    $getSignedURL = URL::temporarySignedRoute(
      'add.pax',
      Carbon::now()->addHours(72),
      ['token' => $token],
      false // Generate relative URL
    );

    // send email to the user
    try {
      $emailTo = $invitation->email;
      Mail::to($emailTo)->send(new AddPassenger($getSignedURL, $booking->booking_code));
    } catch (\Exception $e) {
      \Log::error('Failed to send email to user: ' . $e->getMessage());
      return response()->json(['message' => 'Failed to send email to user'], 500);
    }
  }
}

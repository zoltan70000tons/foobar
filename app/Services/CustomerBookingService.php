<?php

namespace App\Services;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use App\Repositories\CustomerBookingRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\PassengerInvitation;
use App\Mail\AddPassenger;
use App\Mail\AddPassengerDirectly;
use App\Enums\EventStatus;

class CustomerBookingService {
    protected $customerBookingRepository;

    public function __construct(CustomerBookingRepository $customerBookingRepository) {
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
    // public function getMyBookings($user)
    // {
    //   $bookings = $this->customerBookingRepository->getAllBookings($user);

    //   // if booking have status new then do not return cabin id and number
    //   $bookings->map(function ($booking) {
    //     if ($booking->status === 'NEW' || $booking->status === 'CANCELLED') {
    //       $booking->cabin->makeHidden(['cabin_number']);
    //       $booking->cabin->makeHidden(['internal_notes']);
    //       $booking->cabin->cabinSpec->makeHidden(['cabin_number']);
    //     }

    //     // if event of booking is past return only event information
    //     if ($booking->event->status === EventStatus::CLOSED->value) {
    //       $booking->makeHidden([
    //         'bed_config',
    //         'booking_code',
    //         'booking_request_id',
    //         'updated_at',
    //         'created_at',
    //         'customer_id',
    //         'payment_plan',
    //         'is_single_occupancy',
    //         'cabin',
    //       ]);
    //     }
    //   });

    //   return $bookings;
    // }

    /*
  |--------------------------------------------------------------------------
  | Add passenger via email - form
  |--------------------------------------------------------------------------
  |
  | This method will send an email to the user with a signed URL to add a passenger
  | User will get a signed URL to add a passenger to the booking via form
  |
  */
    public function addPassengerViaEmail($booking, $invitation, $email, $fromWho, $toWho, $event, $toWhoLang = null) {
        // generate signed url
        $getSignedURL = URL::temporarySignedRoute(
            'add.pax',
            Carbon::now()->addHours(72),
            ['token' => $invitation->token],
            false,
        );

        // send email to the user
        try {
            Mail::to($email)->queue(
                new AddPassenger($getSignedURL, $booking->booking_code, $fromWho, $toWho, $event, $toWhoLang),
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send email to user: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send email to user'], 500);
        }
    }

    /*
  |--------------------------------------------------------------------------
  | Add passenger via email - directly
  |--------------------------------------------------------------------------
  |
  | We send email but contet is not a signed URL.
  | User will get information to login to his account and check invitations
  |
  */
    public function addPassengerViaEmailDirectly($booking, $email, $fromWho, $toWho, $event, $toWhoLang = null) {
        // send email to the user
        try {
            Mail::to($email)->queue(
                new AddPassengerDirectly($booking->booking_code, $fromWho, $toWho, $event, $toWhoLang),
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send email to user: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send email to user'], 500);
        }
    }
}

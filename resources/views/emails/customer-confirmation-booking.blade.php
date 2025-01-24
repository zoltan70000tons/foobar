<?php
/*
|--------------------------------------------------------------------------
| Customer Booking Confirmation Email
|--------------------------------------------------------------------------
|
| This email is send when customer makes a booking.
|
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Your Booking Request')

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('confirmationBooking.cbe_hello') }} {{ $bookingResult['passenger']['App\\Models\\Passenger']['first_name'] ?? 'N/A' }},</p>
    <p>{{ __('confirmationBooking.cbe_thank_you') }}</p>

    <p>{{ __('confirmationBooking.cbe_please_note') }}</p>

    <p>{{ __('confirmationBooking.cbe_important') }}</p>
    <p>{{ __('confirmationBooking.cbe_following_booking') }}</p>

    <!---- BOOKING INFO ---->
    <table>
        <tr>
            <td>{{ __('confirmationBooking.cbe_booking_type') }}:</td>
            <td>{{ $bookingResult['booking']['App\\Models\\Booking']['cabin']['cabin_type']['cabin_type'] ?? 'N/A' }}</td>
          </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_cabin_category') }}:</td>
            <td><!-- HERE --></td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_form_of_payment') }}:</td>
            <td><!-- HERE --></td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_official_ticket_price_per_person') }}:</td>
            <td><!-- HERE --></td>
        </tr>
        <tr>
          <td>{{ __('confirmationBooking.cbe_pay_in_full_discount') }}:</td>
          <td><!-- HERE --></td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_net_ticket_price_per_person') }}:</td>
            <td><!-- HERE --></td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_taxes_and_fees_per_person') }}:</td>
            <td><!-- HERE --></td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_single_traveler_surcharge') }}:</td>
            <td><!-- HERE --></td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_total_ticket_price') }}:</td>
            <td><!-- HERE --></td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_number_of_passengers') }}:</td>
            <td><!-- HERE --></td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_grand_total_booking_price') }}:</td>
            <td><!-- HERE --></td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_payment_schedule') }}:</td>
            <td><!-- HERE --></td>
        </tr>
    </table>
    <!---- PASS INFO ---->
    <table>
      <tr>
          <td>{{ __('confirmationBooking.cbe_booking_type') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_cabin_category') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_form_of_payment') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_official_ticket_price_per_person') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
        <td>{{ __('confirmationBooking.cbe_pay_in_full_discount') }}:</td>
        <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_net_ticket_price_per_person') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_taxes_and_fees_per_person') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_single_traveler_surcharge') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_total_ticket_price') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_number_of_passengers') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_grand_total_booking_price') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_payment_schedule') }}:</td>
          <td><!-- HERE --></td>
      </tr>
    </table>
    <!---- Lead pass details ---->
    <p>{{ __('confirmationBooking.cbe_lead_passenger_details') }}</p>
    <table>
      <tr>
          <td>{{ __('confirmationBooking.cbe_gender') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_first_name') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_middle_name') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
        <td>{{ __('confirmationBooking.cbe_last_name') }}:</td>
        <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_date_of_birth') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_citizenship') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_address_line_1') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_address_line_2') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_city') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_state') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_postal_code') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_country') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_email') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_phone_number') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_emergency_contact_name') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_emergency_phone_number') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_special_request') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_survivor_referal_number') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_how_did_you_hear_about_us') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_receive_newsletter') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_receive_partner_information') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_accept_bed_configuration') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_accept_terms') }}:</td>
          <td><!-- HERE --></td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_todays_date') }}:</td>
          <td><!-- HERE --></td>
      </tr>
    </table>
@endsection

@section('footer')
    <p>{{ __('confirmationBooking.cbe_questions') }},</p>
    <p>{{ __('confirmationBooking.cbe_or_call') }}</p>
@endsection
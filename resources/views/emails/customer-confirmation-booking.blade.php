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
    <p>{{ __('confirmationBooking.cbe_hello') }} {{ $bookingResult->passenger->first_name  }},</p>
    <p>{{ __('confirmationBooking.cbe_thank_you') }}</p>

    <p>{{ __('confirmationBooking.cbe_please_note') }}</p>

    <p>{{ __('confirmationBooking.cbe_important') }}</p>
    <p>{{ __('confirmationBooking.cbe_following_booking') }}</p>

    <!---- BOOKING INFO ---->
    <table>
        <tr>
            <td>{{ __('confirmationBooking.cbe_booking_type') }}:</td>
            <td>{{ $bookingResult->booking->booking_type }}</td>
          </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_cabin_category') }}:</td>
            <td>{{ $bookingResult->booking->cabin_category }}</td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_form_of_payment') }}:</td>
            <td>{{ $bookingResult->booking->form_of_payment }}</td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_official_ticket_price_per_person') }}:</td>
            <td>{{ $bookingResult->booking->official_ticket_price_per_person }}</td>
        </tr>
        <tr>
          <td>{{ __('confirmationBooking.cbe_pay_in_full_discount') }}:</td>
          <td>{{ $bookingResult->booking->pay_in_full_discount }}</td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_net_ticket_price_per_person') }}:</td>
            <td>{{ $bookingResult->booking->net_ticket_price_per_person }}</td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_taxes_and_fees_per_person') }}:</td>
            <td>{{ $bookingResult->booking->taxes_and_fees_per_person }}</td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_single_traveler_surcharge') }}:</td>
            <td>{{ $bookingResult->booking->single_traveler_surcharge }}</td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_total_ticket_price') }}:</td>
            <td>{{ $bookingResult->booking->total_ticket_price }}</td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_number_of_passengers') }}:</td>
            <td>{{ $bookingResult->booking->number_of_passengers }}</td>
        </tr>
        <tr>
            <td>{{ __('confirmationBooking.cbe_grand_total_booking_price') }}:</td>
            <td>{{ $bookingResult->booking->grand_total_booking_price }}</td>
        </tr>
    </table>
    @if($bookingResult->booking->payment_schedule !== 'N/A')
        <h2>{{ __('confirmationBooking.cbe_grand_total_booking_price') }}</h2>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th>Due Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookingResult->booking->payment_schedule as $installment)
                    <tr>
                        <td>{{ $installment['due_date'] }}</td>
                        <td>USD {{ $installment['amount'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <!---- Lead pass details ---->
    <p>{{ __('confirmationBooking.cbe_lead_passenger_details') }}</p>
    <table>
      <tr>
          <td>{{ __('confirmationBooking.cbe_gender') }}:</td>
          <td>{{ $bookingResult->passenger->gender }}</td>
        </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_first_name') }}:</td>
          <td>{{ $bookingResult->passenger->first_name }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_middle_name') }}:</td>
          <td>{{ $bookingResult->passenger->middle_name }}</td>
      </tr>
      <tr>
        <td>{{ __('confirmationBooking.cbe_last_name') }}:</td>
        <td>{{ $bookingResult->passenger->last_name }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_date_of_birth') }}:</td>
          <td>{{ $bookingResult->passenger->date_of_birth }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_citizenship') }}:</td>
          <td>{{ $bookingResult->passenger->citizenship }}</td>

      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_address_line_1') }}:</td>
          <td>{{ $bookingResult->passenger->address_line_1 }}</td>

      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_address_line_2') }}:</td>
          <td>{{ $bookingResult->passenger->address_line_2 }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_city') }}:</td>
          <td>{{ $bookingResult->passenger->city }}</td>

      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_state') }}:</td>
          <td>{{ $bookingResult->passenger->state }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_postal_code') }}:</td>
          <td>{{ $bookingResult->passenger->postal_code }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_country') }}:</td>
          <td>{{ $bookingResult->passenger->country }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_email') }}:</td>
          <td>{{ $bookingResult->passenger->email }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_phone_number') }}:</td>
          <td>{{ $bookingResult->passenger->phone_number }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_emergency_contact_name') }}:</td>
          <td>{{ $bookingResult->passenger->emergency_contact_name }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_emergency_phone_number') }}:</td>
          <td>{{ $bookingResult->passenger->emergency_phone_number }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_special_request') }}:</td>
          <td>{{ $bookingResult->passenger->special_request }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_survivor_referal_number') }}:</td>
          <td>{{ $bookingResult->passenger->survivor_referal_number }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_how_did_you_hear_about_us') }}:</td>
          <td>{{ $bookingResult->passenger->how_did_you_hear_about_us }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_receive_newsletter') }}:</td>
          <td>{{ $bookingResult->passenger->receive_newsletter }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_receive_partner_information') }}:</td>
          <td>{{ $bookingResult->passenger->receive_partner_information }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_accept_bed_configuration') }}:</td>
          <td>{{ $bookingResult->passenger->accept_bed_configuration }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_accept_terms') }}:</td>
          <td>{{ $bookingResult->passenger->accept_terms }}</td>
      </tr>
      <tr>
          <td>{{ __('confirmationBooking.cbe_todays_date') }}:</td>
          <td>{{ $bookingResult->booking->todays_date }}</td>
      </tr>
    </table>
@endsection

@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection
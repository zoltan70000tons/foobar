<?php
/*
|--------------------------------------------------------------------------
| Customer Reset Seat
|--------------------------------------------------------------------------

This template is used to send information about the reset seat to the customer.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Payment Received')

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.payment_received_greeting', ['name' => $passengerName]) }}</p>
    <p>{{ __('systemEmails.payment_received_body', ['amount' => $paymentAmount, 'booking_code' => $bookingCode]) }}</p>
    <p>{{ __('systemEmails.payment_received_next_steps') }}</p>
    <p>{{ __('systemEmails.payment_received_questions') }}</p>
@endsection
@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection

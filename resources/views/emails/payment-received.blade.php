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
    <p>{{ __('systemEmails.common.greeting.hello', ['name' => $passengerName]) }}</p>
    <p>{!! __('systemEmails.payment.received.body', ['paymentAmount' => $paymentAmount, 'bookingCode' => $bookingCode]) !!}</p>
    <p>{{ __('systemEmails.payment.received.next_steps') }}</p>
    <p>{{ __('systemEmails.payment.received.questions') }}</p>
@endsection
@section('regards')
    <p>{{ __('systemEmails.common.salutation.thanks') }}</p>
    <p>{{ __('systemEmails.common.salutation.regards') }}</p>
@endsection

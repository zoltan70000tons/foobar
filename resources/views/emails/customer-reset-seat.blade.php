<?php
/*
|--------------------------------------------------------------------------
| Customer Reset Seat
|--------------------------------------------------------------------------

This template is used to send information about the reset seat to the customer.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Reset Password')

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>Hi {{ $passengerEmail }},</p>
    <p>We wanted to let you know that your assigned seat in the cabin has been reset.</p>
    <p>If you have any questions, please reach out to the Lead Passenger of your cabin for more information.</p>
@endsection
@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection

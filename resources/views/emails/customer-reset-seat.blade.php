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
    <p>{{ __('systemEmails.seat_reset_notification')}}</p>
    <p>{{ __('systemEmails.seat_reset_questions')}}</p>
@endsection
@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection

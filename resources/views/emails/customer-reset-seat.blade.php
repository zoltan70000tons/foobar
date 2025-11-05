<?php
/*
|--------------------------------------------------------------------------
| Customer Reset Seat
|--------------------------------------------------------------------------

This template is used to send information about the reset seat to the customer.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', __('systemEmails.seat.reset.subject'))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>Hi {{ $passengerEmail }},</p>
    <p>{{ __('systemEmails.seat_reset_notification')}}</p>
    <p>{{ __('systemEmails.seat_reset_questions')}}</p>
@endsection
    <p>{{ __('systemEmails.common.greeting.hello', ['name' => $passengerEmail]) }}</p>
    <p>{{ __('systemEmails.seat.reset.intro') }}</p>
    <p>{{ __('systemEmails.seat.reset.questions') }}</p>
@endsection
@section('regards')
    <p>{{ __('systemEmails.common.salutation.thanks') }}</p>
    <p>{{ __('systemEmails.common.salutation.regards') }}</p>
@endsection
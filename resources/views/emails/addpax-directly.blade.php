<?php
/*
|--------------------------------------------------------------------------
| Add Pax email
|--------------------------------------------------------------------------

This email is send when customer send a request to add a pax.

*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Add Pax Request')

@section('header')
    70000TONS OF METAL
@endsection

@section('content')

    <p>Hello, {{ $toWho }}</p>
    <p>{{ __('systemEmails.email_invitation_on_your_account')}}</p>

    <p>{{ $fromWho }}  {{ __('systemEmails.email_request_to_add_pax')}} {{ $event_name }}</p>

    <p>Booking number: {{ $bookingCode }}</p>
    <p>{{ __('systemEmails.email_invitation_on_your_account_body')}}</p>
    @include('emails.components.button', [
        'url' => $url,
        'slot' => 'Login to your account'
    ])

@endsection

@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection
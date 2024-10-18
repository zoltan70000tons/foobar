<?php

/*
|--------------------------------------------------------------------------
| Customer Regustered Email
|--------------------------------------------------------------------------

This email is send when customer is successfully registered.
We'll send a welcome email to the customer with new survivor number.

*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Account created')

@section('content')
    <p>{{ __('systemEmails.email_hi') }} {{ $customer->name }},</p>
    <p>{{ __('systemEmails.email_excited') }}</p>
    <p>{{ __('systemEmails.email_new_survivor_number') }}
        <strong>{{ $survivorNumber }}</strong>.
    </p>
    <p>{{ __('systemEmails.email_activate_account') }}</p>
    <p>
        <a href="{{ $activationLink }}">{{ __('systemEmails.email_activate_link') }}</a>
    </p>
    <p>{{ __('systemEmails.email_thank_you') }}</p>
@endsection

@section('footer')
    <p>{{ __('systemEmails.email_regards') }},</p>
    <p>{{ config('app.name') }}</p>
@endsection
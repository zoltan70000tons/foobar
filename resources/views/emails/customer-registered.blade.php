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

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.email_hi') }} {{ $customer->username }},</p>
    <p>{{ __('systemEmails.email_excited') }}</p>
    <p>{{ __('systemEmails.email_account_created') }}
    <p>{{ __('systemEmails.email_new_survivor_number') }}
        <strong>{{ $survivorNumber }}</strong>.
    </p>
    <p>{{ __('systemEmails.email_activate_account') }}</p>
    <p>
        <a href="{{ $activationLink }}">{{ $activationLink }}</a>
    </p>
    <p>{{ __('systemEmails.email_thank_you') }}</p>
@endsection

@section('footer')
    <p>{{ __('systemEmails.email_regards') }},</p>
    <p>70000TONS OF METAL TEAM</p>
@endsection
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
    <p>{{ __('systemEmails.email_hi') }} {{ $customer->detail->first_name }},</p>
    <p>{{ __('systemEmails.email_excited') }}</p>
    <p>{{ __('systemEmails.email_account_created') }}
    <p>{{ __('systemEmails.email_new_survivor_number') }}
        <strong>{{ $survivorNumber }}</strong>.
    </p>
    <p>{{ __('systemEmails.email_activate_account') }}</p>
    @include('emails.components.button', [
        'url' => $activationLink,
        'slot' => __('systemEmails.email_cta_activate_account')
    ])
    <p>{{ __('systemEmails.email_cant_see_button') }}</p>

    @include('emails.components.long-string', [
        'url' => $activationLink,
        'slot' => $activationLink
    ])
@endsection

@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection
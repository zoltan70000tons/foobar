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

@section('title', __('systemEmails.account.activation.title'))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.common.greeting.hello', ['name' => $customer->detail->first_name]) }}</p>
    <p>{{ __('systemEmails.account.activation.welcome') }}</p>
    <p>{{ __('systemEmails.account.activation.created') }}</p>
    <p>{{ __('systemEmails.account.activation.survivor_number_label') }} <strong>{{ $survivorNumber }}</strong>.</p>
    <p>{{ __('systemEmails.account.activation.cta_intro') }}</p>
    @include('emails.components.button', [
        'url' => $activationLink,
        'slot' => __('systemEmails.account.activation.cta_label')
    ])
    <p>{{ __('systemEmails.common.cta_help') }}</p>
    @include('emails.components.long-string', [
        'url' => $activationLink,
        'slot' => $activationLink
    ])
@endsection
@section('regards')
    <p>{{ __('systemEmails.common.salutation.thanks') }}</p>
    <p>{{ __('systemEmails.common.salutation.regards') }}</p>
@endsection
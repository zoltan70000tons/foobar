<?php
/*
|--------------------------------------------------------------------------
| Add Pax email
|--------------------------------------------------------------------------

This email is send when customer send a request to add a pax.

*/
?>

@extends('emails.layouts.systemLayout')

@section('title', __('systemEmails.invitation.request.title'))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.common.greeting.hello', ['name' => $toWho]) }}</p>
    <p>{{ __('systemEmails.invitation.request.account_notice') }}</p>
    <p>{{ __('systemEmails.invitation.request.headline', ['from' => $fromWho, 'event' => $event_name]) }}</p>
    <p>{{ __('systemEmails.invitation.request.booking_code_label') }}: {{ $bookingCode }}</p>
    <p>{{ __('systemEmails.invitation.request.account_instructions') }}</p>
    @include('emails.components.button', [
        'url' => $url,
        'slot' => __('systemEmails.invitation.request.cta.login')
    ])
    <p>{{ __('systemEmails.common.cta_help') }}</p>
    @include('emails.components.long-string', [
        'url' => $url,
        'slot' => $url
    ])
@endsection
@section('regards')
    <p>{{ __('systemEmails.common.salutation.thanks') }}</p>
    <p>{{ __('systemEmails.common.salutation.regards') }}</p>
@endsection
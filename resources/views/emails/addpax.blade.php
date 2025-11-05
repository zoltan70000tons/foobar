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
    <p>{{ __('systemEmails.invitation.request.headline', ['from' => $fromWho, 'event' => $event_name]) }}</p>
    <p>{{ __('systemEmails.invitation.request.instructions') }}</p>
    @include('emails.components.button', [
        'url' => $getSignedURL,
        'slot' => __('systemEmails.invitation.request.cta.complete_form')
    ])
    <p>{{ __('systemEmails.common.cta_help') }}</p>
    @include('emails.components.long-string', [
        'url' => $getSignedURL,
        'slot' => $getSignedURL
    ])
@endsection
@section('regards')
    <p>{{ __('systemEmails.common.salutation.thanks') }}</p>
    <p>{{ __('systemEmails.common.salutation.regards') }}</p>
@endsection
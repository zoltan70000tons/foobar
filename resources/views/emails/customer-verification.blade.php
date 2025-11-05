<?php
/*
|--------------------------------------------------------------------------
| Customer Re send Verification Email
|--------------------------------------------------------------------------
| This template is used to send a verification email to the customer. 
|
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', __('systemEmails.account.verification.title'))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.common.greeting.hello', ['name' => $user->detail->first_name ?? __('systemEmails.common.greeting.default_name')]) }}</p>
    <p>{{ __('systemEmails.account.verification.instruction') }}</p>
    @include('emails.components.button', [
        'url' => $verificationUrl,
        'slot' => __('systemEmails.account.verification.cta_label')
    ])
    <p>{{ __('systemEmails.common.cta_help') }}</p>
    @include('emails.components.long-string', [
        'url' => $verificationUrl,
        'slot' => $verificationUrl
    ])
    <p>{{ __('systemEmails.account.verification.fallback') }}</p>
@endsection

@section('regards')
    <p>{{ __('systemEmails.common.salutation.thanks') }}</p>
    <p>{{ __('systemEmails.common.salutation.regards') }}</p>
@endsection
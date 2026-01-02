<?php
/*
|--------------------------------------------------------------------------
| Customer Reset Password Email
|--------------------------------------------------------------------------

This template is used to send a reset password link to the customer.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', __('systemEmails.password.reset.title'))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.common.greeting.hello', ['name' => $customerName]) }}</p>
    <p>{{ __('systemEmails.password.reset.intro') }}</p>
    @include('emails.components.button', [
        'url' => $resetUrl,
        'slot' => __('systemEmails.password.reset.cta_label')
    ])
    <p>{{ __('systemEmails.common.cta_help') }}</p>

    @include('emails.components.long-string', [
        'url' => $resetUrl,
        'slot' => $resetUrl
    ])
@endsection
@section('regards')
    <p>{{ __('systemEmails.common.salutation.thanks') }}</p>
    <p>{{ __('systemEmails.common.salutation.regards') }}</p>
@endsection

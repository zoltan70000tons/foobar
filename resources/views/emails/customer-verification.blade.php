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

@section('title', 'Verify Your Email Address')

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>Hello {{ $user->detail->first_name ?? 'Sailor' }},</p>
    <p>Please click the button below to verify your email address:</p>

    @include('emails.components.button', [
        'url' => $verificationUrl,
        'slot' => 'Verify Email Address'
    ])

    <p>If you did not create an account, no further action is required.</p>
@endsection

@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection
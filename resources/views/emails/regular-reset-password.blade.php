<?php
/*
|--------------------------------------------------------------------------
| Regular Reset Password Email
|--------------------------------------------------------------------------

This template is used to send a reset password link to the customer.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Reset Password')

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>Hi,</p>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    @include('emails.components.button', [
        'url' => $resetUrl,
        'slot' => 'Reset Password'
    ])
    <p>{{ __('systemEmails.email_cant_see_button') }}</p>

    @include('emails.components.long-string', [
        'url' => $resetUrl,
        'slot' => $resetUrl
    ])
    
@endsection
@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection

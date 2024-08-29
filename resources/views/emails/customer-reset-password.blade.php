<?php

/*
|--------------------------------------------------------------------------
| Customer Reset Password Email
|--------------------------------------------------------------------------

This template is used to send a reset password link to the customer.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Reset Password')

@section('content')
    <p>Hi {{ $customer->name }},</p>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p><a href="{{ $resetUrl }}">Reset Password</a></p>
    <p>If you did not request a password reset, no further action is required.</p>
@endsection

@section('footer')
    <p>Regards,</p>
    <p>{{ config('app.name') }}</p>
@endsection

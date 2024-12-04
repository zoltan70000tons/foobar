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

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>Hi {{ $customer->username }},</p>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p><a href="{{ $resetUrl }}">Reset Password</a></p>
    <p>If you did not request a password reset, no further action is required.</p>
@endsection

@section('footer')
    <p>{{ __('systemEmails.email_regards') }},</p>
    <p>70000TONS OF METAL TEAM</p>
@endsection

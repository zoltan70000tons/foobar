<?php
/*
|--------------------------------------------------------------------------
| Customer Reset Password Confirmation Email
|--------------------------------------------------------------------------

This template is used to send confirmation email to the customer after resetting the password.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', __('systemEmails.reset_password_success_title'))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.reset_password_hi') }} {{ $customer->detail->first_name }},</p>
    <p>{{ __('systemEmails.reset_password_success_body') }}</p>
@endsection

@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection
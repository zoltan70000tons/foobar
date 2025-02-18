<?php
/*
|--------------------------------------------------------------------------
| Customer Reset Password Confirmation Email
|--------------------------------------------------------------------------

This template is used to send confirmation email to the customer after resetting the password.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Reset Password')

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>Hi {{ $customer->detail->first_name }},</p>
    <p>We would like to inform you that your password has been successfully reset.</p>
@endsection

@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection
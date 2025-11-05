<?php
/*
|--------------------------------------------------------------------------
| Customer Reset Password Confirmation Email
|--------------------------------------------------------------------------

This template is used to send confirmation email to the customer after resetting the password.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', __('systemEmails.password.confirmation.title'))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.common.greeting.hello', ['name' => $customerName]) }},</p>
    <p>{{ __('systemEmails.password.confirmation.intro') }}</p>
@endsection

@section('regards')
    <p>{{ __('systemEmails.common.salutation.thanks') }}</p>
    <p>{{ __('systemEmails.common.salutation.regards') }}</p>
@endsection
<?php
/*
|--------------------------------------------------------------------------
| Customer Reset Password Email
|--------------------------------------------------------------------------

This template is used to send a reset password link to the customer.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', __('systemEmails.reset_password_title'))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.reset_password_hi') }} {{ $customer->detail->first_name }},</p>
    <p>{{ __('systemEmails.reset_password_body') }}</p>
    @include('emails.components.button', [
        'url' => $resetUrl,
        'slot' => __('systemEmails.reset_password_btn_text')
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

<?php
/*
|--------------------------------------------------------------------------
| Activate Survivor Email
|--------------------------------------------------------------------------
|
| This email is send when survivor is successfully activated.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Account activated')

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.email_hi') }} {{ $customer->first_name }},</p>
    <p>{{ __('systemEmails.email_excited') }}</p>
    <p>{{ __('systemEmails.email_activated_account') }}</p>


@endsection

@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection
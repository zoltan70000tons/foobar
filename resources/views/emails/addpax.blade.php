<?php
/*
|--------------------------------------------------------------------------
| Add Pax email
|--------------------------------------------------------------------------

This email is send when customer send a request to add a pax.

*/
?>

@extends('emails.layouts.systemLayout')

@section('title', 'Add Pax')

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>You got a request to add a pax.</p>
    From:
        <strong>{{ $survivorNumber }}</strong>.
    </p>
    <p>You have 24 hours to add your details to the booking</p>
    <p>
        <a href="{{ $getSignedURL }}">{{ $getSignedURL }}</a>
    </p>
@endsection

@section('footer')
    <p>{{ __('systemEmails.email_regards') }},</p>
    <p>70000TONS OF METAL TEAM</p>
@endsection
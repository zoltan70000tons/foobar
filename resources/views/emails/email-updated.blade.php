<?php
/*
|--------------------------------------------------------------------------
| Customer Update Email
|--------------------------------------------------------------------------

This template is used to send a notifiation to the old customer's email when this is updated.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', __('systemEmails.update_email.title', [], $language))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.update_email.greeting', ['name' => $user->detail->first_name ?? __('systemEmails.update_email.default_name')], $language) }}</p>

    <p>{{ __('systemEmails.update_email.body', [], $language) }}</p>

    <p>{{ __('systemEmails.update_email.security_notice', [], $language) }}</p>
@endsection

@section('regards')
    <p>{{ __('systemEmails.email_thanks') }}</p>
    <p>{{ __('systemEmails.email_regards') }}</p>
@endsection

<?php
/*
|--------------------------------------------------------------------------
| Customer Update Email
|--------------------------------------------------------------------------

This template is used to send a notifiation to the old customer's email when this is updated.
*/
?>

@extends('emails.layouts.systemLayout')

@section('title', __('systemEmails.account.update.title', [], $language))

@section('header')
    70000TONS OF METAL
@endsection

@section('content')
    <p>{{ __('systemEmails.account.update.greeting', ['name' => $user->detail->first_name ?? __('systemEmails.account.update.default_name')], $language) }}</p>

    <p>{{ __('systemEmails.account.update.body', [], $language) }}</p>

    <p>{{ __('systemEmails.account.update.security_notice', [], $language) }}</p>
@endsection

@section('regards')
    <p>{{ __('systemEmails.common.salutation.thanks') }}</p>
    <p>{{ __('systemEmails.common.salutation.regards') }}</p>
@endsection
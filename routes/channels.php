<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel("App.Models.User.{id}", function ($user, $id) {
  return (int) $user->id === (int) $id;
});

Broadcast::channel("temporary-reservations", function () {
  return true;
});

Broadcast::channel("bookings-locked", function () {
  return true;
});

Broadcast::channel("test-channel", function () {
  return true;
});

// For Reverb lock booking while user is editing the booking
Broadcast::channel("reverb-lock-booking", function () {
  return true;
});

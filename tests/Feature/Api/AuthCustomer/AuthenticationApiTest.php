<?php

use App\Models\User;
use App\Models\SurvivorNumber;
use App\Helpers\CustomerHelper;

// This test is for the authentication of users using their email address and password.
test('users can authenticate with survivor number', function () {
  $user = User::factory()->create();

  $this->withHeaders([
    'referer' => env('SANCTUM_STATEFUL_DOMAINS'),
  ]);

  setPermissionsTeamId(1);
  $user->assignRole('Customer');

  $response = $this->postJson('/api/login-customer', [
    'identifier' => $user->email,
    'password' => 'password',
    'remember' => false,
  ]);

  $response->assertOk();
});

// Test for survivor number authentication
test('users can authenticate with email address', function () {
  $user = User::factory()->create();

  $this->withHeaders([
    'referer' => env('SANCTUM_STATEFUL_DOMAINS'),
  ]);

  setPermissionsTeamId(1);
  $user->assignRole('Customer');

  // Generate a unique survivor number
  $survivorNumber = CustomerHelper::generateSurvivorNumber();

  // Save survivor number in the survivor_numbers table
  SurvivorNumber::create([
    'user_id' => $user->id,
    'survivor_number' => $survivorNumber,
  ]);

  $response = $this->postJson('/api/login-customer', [
    'identifier' => $user->survivorNumber->survivor_number,
    'password' => 'password',
    'remember' => false,
  ]);

  $response->assertOk();
});

// Test for invalid credentials
test('user without set team id cannot authenticate', function () {
  app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

  $user = User::factory()->create();

  $this->withHeaders([
    'referer' => env('SANCTUM_STATEFUL_DOMAINS'),
  ]);

  $response = $this->postJson('/api/login-customer', [
    'identifier' => $user->email,
    'password' => 'password',
    'remember' => false,
  ]);

  // expected 403 or 401
  $response->assertForbidden();
});

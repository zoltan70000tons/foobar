<?php

use App\Models\User;

test('users can authenticate with survivor number', function () {
    $user = User::factory()->create();

    $this->withHeaders([
      'referer' => env('SANCTUM_STATEFUL_DOMAINS'),
    ]);

    $response = $this->postJson('/api/login-customer', [
      'survivor_number' => $user->survivor_number,
      'password' => 'password',
      'language' => 'en',
    ]);

    $response->assertStatus(200);

    $response->assertOk();
});

test('customer cant authenticate with invalid survivor number', function () {
    $this->withHeaders([
      'referer' => env('SANCTUM_STATEFUL_DOMAINS'),
    ]);

    $response = $this->postJson('/api/login-customer', [
      'survivor_number' => 'wrong-survivor_number',
      'password' => 'password',
      'language' => 'en',
    ]);

    $response->assertStatus(401);
});

test('customer cant access to web routes', function () {
    $user = User::factory()->create();

    $this->withHeaders([
      'referer' => env('SANCTUM_STATEFUL_DOMAINS'),
    ]);

    $response = $this->postJson('/api/login-customer', [
      'survivor_number' => $user->survivor_number,
      'password' => 'password',
      'language' => 'en',
    ]);

    $response->assertStatus(200);

    $response->assertOk();

    $response = $this->get('/login');

    $response->assertStatus(401);
});

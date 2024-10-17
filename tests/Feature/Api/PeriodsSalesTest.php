<?php

use App\Models\User;
use App\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;


test('periods sales customer type doesnt have access', function () {
    $user = User::factory()->create();

    Membership::factory()->create([
      'customer_id' => $user->id,
      'membership_id' => 4,
    ]);

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

    $response = $this->getJson('/api/events/1');

    $response->assertStatus(403);
});

test('periods sales customer type has access', function () {
    $user = User::factory()->create();

    Membership::factory()->create([
      'customer_id' => $user->id,
      'membership_id' => 1,
    ]);

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

    $response = $this->getJson('/api/events/1');

    $response->assertStatus(200);
});

test('periods sales customer doesnt have membership', function () {
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

    $response = $this->getJson('/api/events/1');

    $response->assertStatus(403);
});

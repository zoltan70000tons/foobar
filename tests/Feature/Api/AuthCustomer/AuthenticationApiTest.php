<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Customer;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
  //use RefreshDatabase;

  public function test_users_can_authenticate_with_survivor_number(): void
  {

    $user = Customer::factory()->create();

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
  }


  public function test_customer_cant_authenticate_with_invalid_survivor_number(): void
  {

    $this->withHeaders([
      'referer' => env('SANCTUM_STATEFUL_DOMAINS'),
    ]);

    $response = $this->postJson('/api/login-customer', [
      'survivor_number' => 'wrong-survivor_number',
      'password' => 'password',
      'language' => 'en',
    ]);

    $response->assertStatus(401);
  }


  public function test_customer_cant_access_to_web_routes(): void
  {
    $user = Customer::factory()->create();

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
  }
}

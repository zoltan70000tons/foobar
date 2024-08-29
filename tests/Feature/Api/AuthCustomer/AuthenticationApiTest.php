<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthenticationApiTest extends TestCase
{
  //use RefreshDatabase;

  public function test_users_can_authenticate_using_the_login_screen(): void
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


  // public function test_customer_in_sessions_have_type_of_guard(): void
  // {
  //   $customer = Customer::factory()->create();

  //   // Send a login request
  //   $response = $this->postJson('/api/login-customer', [
  //     'survivor_number' => $customer->survivor_number,
  //     'password' => 'password',
  //     'language' => 'en',
  //   ]);

  //   // Assert the response is successful
  //   $response->assertStatus(200);
  //   $response->assertOk();

  //   // Use actingAs to simulate the authenticated customer
  //   $response = $this->actingAs($customer, 'customer')
  //     ->withSession(['type_of_guard' => 'customer'])
  //     ->get('/api/customer');

  //   // Assert the response for the authenticated customer is successful
  //   $response->assertStatus(200);
  // }
}

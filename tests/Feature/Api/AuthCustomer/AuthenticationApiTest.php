<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Log;

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
      'survival_number' => $user->survival_number,
      'password' => 'password',
      'language' => 'en',
    ]);

    $response->assertStatus(200);

    $response->assertOk();
  }


  public function test_customer_cant_authenticate_with_invalid_survival_number(): void
  {
    $user = Customer::factory()->create();

    $this->withHeaders([
      'referer' => env('SANCTUM_STATEFUL_DOMAINS'),
    ]);

    $response = $this->postJson('/api/login-customer', [
      'survival_number' => 'wrong-survival_number',
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
      'survival_number' => $user->survival_number,
      'password' => 'password',
      'language' => 'en',
    ]);

    $response->assertStatus(200);

    $response->assertOk();

    $response = $this->get('/login');

    $response->assertStatus(401);
  }
}

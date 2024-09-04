<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Membership;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PeriodsSalesTest extends TestCase
{
  // use RefreshDatabase;

  // we test the customer with membership type 5 doesn't have access to the events
  // because date for this type of membership is not available
  public function test_periods_sales_customer_type_doesnt_have_access(): void
  {

    $user = Customer::factory()->create();

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
  }


  // Here we test that a customer with a membership type of 1 can access the events
  // because the date for this type of membership is available
  public function test_periods_sales_customer_type_has_access(): void
  {

    $user = Customer::factory()->create();

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
  }

  // customer doesn't have any membership type
  // so we validate that customer as regular user can't access the events
  public function test_periods_sales_customer_doesnt_have_membership(): void
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

    $response = $this->getJson('/api/events/1');

    $response->assertStatus(403);
  }
}

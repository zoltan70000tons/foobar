<?php

use App\Models\User;
use App\Models\UserDetail;
use App\Models\Membership;
use App\Models\SurvivorNumber;
use App\Helpers\CustomerHelper;
use function Pest\Faker\fake;

// If user not exist in passenger table, he cant manage his bookings
/*function setupTestCustomerWithMembership(int $membershipId = 4): User
{
  $user = User::factory()->create();

  UserDetail::create([
    'user_id' => $user->id,
    'gender' => fake()->randomElement(['M', 'F']),
    'first_name' => strtoupper('TEST-' . fake()->firstname),
    'middle_name' => strtoupper(fake()->firstName),
    'last_name' => strtoupper(fake()->lastName),
    'dob' => '1999-01-01',
    'citizenship' => fake()->countryISOAlpha3(),
    'phone' => fake()->e164PhoneNumber(), 
    'avatar' => fake()->imageUrl(),
    'emergency_c_name' => fake()->name,
    'emergency_c_phone' => fake()->e164PhoneNumber(),
    'language' => fake()->randomElement(['es', 'de', 'en']),
  ]);

  Membership::create([
    'user_id' => $user->id,
    'membership_id' => $membershipId,
  ]);

  setPermissionsTeamId(1);
  $user->assignRole('Customer');

  $survivorNumber = CustomerHelper::generateSurvivorNumber();
  SurvivorNumber::create([
    'user_id' => $user->id,
    'survivor_number' => $survivorNumber,
  ]);

  test()->withHeaders(['referer' => env('SANCTUM_STATEFUL_DOMAINS')]);

  $response = test()->postJson('/api/login-customer', [
    'identifier' => $survivorNumber,
    'password' => 'password',
    'language' => 'en',
  ]);

  test()->actingAs($user);
  $response->assertOk();

  return $user;
}

// get some booking and passenger data
it('returns an error if user is not a passenger', function () {
  $user = setupTestCustomerWithMembership(); 

  // Use this if your route requires auth
  $this->actingAs($user);

  $response = $this->getJson('/api/my-bookings/1/9708AGJX-F14R');

  $response->assertStatus(404); 
  $response->assertJson([
      'message' => 'Booking not found'
  ]);
});*/

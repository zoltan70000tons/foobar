<?php

use App\Models\User;
use App\Models\UserDetail;
use App\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\SurvivorNumber;
use App\Helpers\CustomerHelper;
use function Pest\Faker\fake;

// test the user can
test('periods sales customer type doesnt have access', function () {
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
    'language' => fake()->randomElement(['es', 'de', 'en']), // Only ESP, DEU, or ENG
  ]);

  Membership::create([
    'user_id' => $user->id,
    'membership_id' => 4,
  ]);

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
    'language' => 'en',
  ]);

  $this->actingAs($user);

  $response->assertStatus(200);

  $response->assertOk();

  $response = $this->getJson('/api/events/1');

  $response->assertStatus(200);

  $payload = [
    'event_id' => '1',
    'cabin_type' => 'private-cabin',
    'cabin_code' => '2V',
    'cabin_capacity' => 2,
    'cabin_category' => 6,
    'cabin_category_decks' => '2,3,6,7,8,9,10',
    'cabin_full_title' => 'Standard Interior 2V',
    'cabin_category_type' => 'Interior',
    'cabin_price' => '2066.00',
    'single_t_agreement' => false,
    'addons' => [
      [
        'id' => 15,
        'code' => 'SERVICE_FEE',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => '150.00',
        'restrictions' => null,
        'event_id' => 2,
        'created_at' => '2025-04-14T10:00:00.000000Z',
        'updated_at' => '2025-04-14T10:00:00.000000Z',
        'system' => true,
      ],
      [
        'id' => 5,
        'code' => 'MEMBERSHIP_GOLD',
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => '10.00',
        'restrictions' => null,
        'event_id' => 2,
        'created_at' => '2025-04-14T10:00:00.000000Z',
        'updated_at' => '2025-04-14T10:00:00.000000Z',
        'system' => true,
      ],
    ],
    'reservation_id' => null,
    'reservation_timestamp' => null,
    'step' => 3,
    'force_clear' => true,
  ];

  $response = $this->postJson('/api/cart', $payload);

  // except "PRESALE_NO_ACCOUNT" message
  $response->assertJson([
    'message' => 'PRESALE_NO_ACCOUNT',
  ]);
});

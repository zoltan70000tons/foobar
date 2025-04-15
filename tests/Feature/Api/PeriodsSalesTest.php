<?php

use App\Models\User;
use App\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\SurvivorNumber;
use App\Helpers\CustomerHelper;

// test the user can
test('periods sales customer type doesnt have access', function () {
  $user = User::factory()->create();

  Membership::factory()->create([
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

  $response->assertStatus(200);

  $response->assertOk();

  $response = $this->getJson('/api/events/1');

  $response->assertStatus(200);

  $payload = [
    'event_id' => '2',
    'cabin_type' => 'shared-cabin',
    'cabin_code' => '3AB',
    'cabin_capacity' => 4,
    'cabin_category' => 3,
    'cabin_category_decks' => '5,6,7',
    'cabin_full_title' => 'Deluxe Ocean View 3AB',
    'cabin_category_type' => 'Ocean View',
    'cabin_price' => '2499.00',
    'single_t_agreement' => true,
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
    'reservation_id' => 2,
    'reservation_timestamp' => '2025-04-14T12:00:00.000000Z',
    'step' => 3,
    'force_clear' => false,
  ];

  $response = $this->postJson('/api/cart', $payload);

  $response->assertStatus(400);
});

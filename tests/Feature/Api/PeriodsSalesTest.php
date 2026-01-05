<?php

use App\Models\User;
use App\Models\UserDetail;
use App\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\SurvivorNumber;
use App\Helpers\CustomerHelper;
use function Pest\Faker\fake;

// --- SCHEMA
// USER
function setupTestCustomerWithMembership(int $membershipId = 4): User {
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

// CART
function baseCartPayload(): array {
    return [
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
                'event_id' => 2,
                'system' => true,
            ],
            [
                'id' => 5,
                'code' => 'MEMBERSHIP_GOLD',
                'type' => 'DISCOUNT',
                'operation' => 'PERCENTAGE',
                'value' => '10.00',
                'event_id' => 2,
                'system' => true,
            ],
        ],
        'reservation_id' => null,
        'reservation_timestamp' => null,
        'step' => 3,
        'force_clear' => true,
    ];
}

// ---------------- test the user can not post to cart
test('user with no access cannot post to cart', function () {
    setupTestCustomerWithMembership(4);

    $response = $this->postJson('/api/cart', baseCartPayload());

    $response->assertJson([
        'access_message' => [
            'code' => 'NO_MEMBERSHIP_ACCESS',
        ],
    ]);
});

// ---------------- test the user can not update cart
test('user with no access cannot update cart', function () {
    setupTestCustomerWithMembership(4);

    $response = $this->putJson('/api/cart', baseCartPayload());

    $response->assertJson([
        'access_message' => [
            'code' => 'NO_MEMBERSHIP_ACCESS',
        ],
    ]);
});

// ---------------- test the user can not delete cart
test('user with no access cannot delete cart', function () {
    setupTestCustomerWithMembership(4);

    $response = $this->deleteJson('/api/cart?event_id=1');

    $response->assertJson([
        'access_message' => [
            'code' => 'NO_MEMBERSHIP_ACCESS',
        ],
    ]);
});

// ---------------- test the user CAN get card without access
test('user with no access can get cart', function () {
    setupTestCustomerWithMembership(4);

    $response = $this->getJson('/api/cart/1');

    $response->assertOk();
});

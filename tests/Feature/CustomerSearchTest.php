<?php

use App\Models\User;
use App\Models\UserDetail;
use App\Models\SurvivorNumber;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(DatabaseTransactions::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    setPermissionsTeamId(config('settings.organization_id'));
    Role::findOrCreate('Customer', 'web');
    Role::findOrCreate('Admin', 'web');
});

it('returns only customers matching by email or name', function () {
    $authUser = User::factory()->create();
    $this->actingAs($authUser);

    $customer = User::factory()->create(['email' => 'john.doe@example.com']);
    $customer->assignRole(Role::findByName('Customer', 'web'));

    UserDetail::factory()->create([
        'user_id' => $customer->id,
        'first_name' => 'JOHN',
        'last_name' => 'DOE',
        'citizenship' => 'Santiago',
    ]);

    SurvivorNumber::factory()->create([
        'user_id' => $customer->id,
        'survivor_number' => '12345678',
    ]);

    $admin = User::factory()->create(['email' => 'dave@example.com']);
    $admin->assignRole(Role::findByName('Admin', 'web'));
    UserDetail::factory()->create([
        'user_id' => $admin->id,
        'first_name' => 'DAVE',
        'last_name' => 'SMITH',
    ]);

    $res = $this->getJson('passengers/search?query=john')->assertOk();

    $json = $res->json();

    expect($json)->toHaveCount(1);
    expect($json[0]['email'])->toBe('john.doe@example.com');
    expect($json[0]['first_name'])->toBe('JOHN');
    expect($json[0]['last_name'])->toBe('DOE');
    expect($json[0]['survivor_number'])->toBe('12345678');
    expect($json[0]['has_booking'])->toBeFalse();
});

<?php

use App\Enums\Permissions;
use App\Models\User;
use App\Repositories\TeamRepository;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;


beforeEach(function () {
    $orgId = 1;
    config(['settings.organization_id' => $orgId]);
    app(PermissionRegistrar::class)->setPermissionsTeamId($orgId);
});



it('lists the organization members when the user has permission', function () {
    config(['settings.organization_id' => 1]);

    /** @var User $user */
    $user = User::factory()->create();
    $user->givePermissionTo(Permissions::ViewUsers->value);

    $members = [
        ['name' => 'John'],
        ['name' => 'Jane'],
    ];

    $repoMock = Mockery::mock(TeamRepository::class);
    $repoMock->shouldReceive('getAllMembers')
        ->once()
        ->with(1)
        ->andReturn($members);

    app()->instance(TeamRepository::class, $repoMock);

    $response = $this
        ->actingAs($user)
        ->getJson(route('team.members.list'));

    $response
        ->assertOk()
        ->assertExactJson($members);
});

it('rejects listing members when the user lacks permission', function () {
    config(['settings.organization_id' => 1]);

    /** @var User $user */
    $user = User::factory()->create();

    $repoMock = Mockery::mock(TeamRepository::class);
    app()->instance(TeamRepository::class, $repoMock);

    $response = $this
        ->actingAs($user)
        ->getJson(route('team.members.list'));
    $response->assertStatus(302);
});

it('updates a member’s roles and redirects with a success message', function () {
    config(['settings.organization_id' => 1]);

    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('Manager');

    $member = User::factory()->create();
    $member->assignRole('Agent');

    $payload = [
        'user_id' => Str::uuid()->toString(),
        'roles'   => ['Admin', 'Editor'], 
    ];

    $repoMock = Mockery::mock(TeamRepository::class);
     $repoMock->shouldReceive('updateMemberRoles')
        ->once()
        ->withArgs(function ($userId, $organizationId, $roles) use ($payload) {
            expect($userId)->toBe($payload['user_id']);
            expect($organizationId)->toBe(1);
            expect($roles)->toMatchArray($payload['roles']);
            return true;
        })
        ->andReturnTrue();

    app()->instance(TeamRepository::class, $repoMock);

    $response = $this
        ->from('/teams') 
        ->actingAs($user)
        ->put(route('member.updateRole'), $payload);

    $response->assertRedirect('/teams')
        ->assertSessionHas('success', 'User role updated successfully');
});

it('rejects updating roles when the user lacks permission', function () {
    config(['settings.organization_id' => 123]);

    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('Agent');

    $member = User::factory()->create();
    $member->assignRole('Agent');

    $payload = [
        'user_id' => Str::uuid()->toString(),
        'roles'   => ['Admin', 'Editor'], 
    ];

    $repoMock = Mockery::mock(TeamRepository::class);
    app()->instance(TeamRepository::class, $repoMock);

    $response = $this
        ->actingAs($user)
        ->put(route('member.updateRole'), $payload);

    $response->assertStatus(302); 
});

it('updates a member and redirects to the teams route with a message', function () {
    config(['settings.organization_id' => 1]);

    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('Manager');
    $member = User::factory()->create();
    $member->assignRole('Agent');

    $payload = [
        'id'    => $member->id,
        'firstname'  => 'Caroline',
        'lastname'  => 'Anderson',
        'gender' => 'F',
        'email' => 'new@example.com',

    ];

    $repoMock = Mockery::mock(TeamRepository::class);
    $repoMock->shouldReceive('updateMember')
        ->once()
        ->with($payload)
        ->andReturnTrue();

    app()->instance(TeamRepository::class, $repoMock);

    $response = $this
        ->actingAs($user)
        ->post(route('member.update'), $payload);

    $response
        ->assertRedirect(route('teams', ['slug' => '70K']))
        ->assertSessionHas('message', 'User updated successfully');
});

it('returns the Inertia error view if updateMember throws an exception', function () {
    config(['settings.organization_id' => 1]);

    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('Manager');
    $member = User::factory()->create();
    $member->assignRole('Agent');


    $payload = [
        'id'    => $member->id,
        'firstname'  => 'David',
        'lastname'  => 'Smith',
        'gender' => 'M',
        'email' => 'new@example.com',

    ];
    $repoMock = Mockery::mock(TeamRepository::class);
    $repoMock->shouldReceive('updateMember')
        ->once()
        ->with($payload)
        ->andThrow(new Exception('Boom'));

    app()->instance(TeamRepository::class, $repoMock);

    $response = $this
        ->actingAs($user)
        ->post(route('member.update'), $payload);

    $response->assertInertia(function (Assert $page) {
        $page->component('Teams')
             ->where('errors', 'Boom');
    });
});

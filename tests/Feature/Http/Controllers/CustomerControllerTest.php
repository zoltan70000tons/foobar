<?php

use App\Models\User;
use App\Models\Tag;
use App\Models\Booking;
use App\Models\SurvivorNumber;
use App\Models\UserComment;
use App\Services\CustomerService;
use App\Repositories\CustomerRepository;
use Dom\Comment;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\ActsAsAgent;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\patch;
use function Pest\Laravel\delete as httpDelete;
use function Pest\Laravel\put;

uses(DatabaseTransactionsManager::class, ActsAsAgent::class)->in('Feature');

beforeAll(function () {});

beforeEach(function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $this->actingAs($user, 'web');
    app(PermissionRegistrar::class)->setPermissionsTeamId(1);
    $role = Role::firstOrCreate(['name' => 'Agent', 'guard_name' => 'web']);
    $perm = Permission::findOrCreate('CreateCustomers', 'web');
    $role->givePermissionTo($perm);
    $user->assignRole($role);
});

// Helpers para DI
function bindRepoService(?Closure $repoMocker = null, ?Closure $svcMocker = null): array {
    $repo = Mockery::mock(CustomerRepository::class);
    $svc = Mockery::mock(CustomerService::class);

    if ($repoMocker) {
        $repoMocker($repo);
    }
    if ($svcMocker) {
        $svcMocker($svc);
    }

    app()->instance(CustomerRepository::class, $repo);
    app()->instance(CustomerService::class, $svc);

    return [$repo, $svc];
}

// ---------- index
it('index: renders inertia with customers and tags', function () {
    $items = collect([]);
    $paginator = new LengthAwarePaginator($items, $items->count(), 15, 1);

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) use ($paginator) {
            $m->shouldReceive('getAllCustomerData')->once()->andReturn($paginator); //
        },
    );
    // Tag::query()->delete();

    $res = get(route('customers.index'));

    $res->assertOk()->assertInertia(
        fn(Assert $page) => $page->component('Customer/Index')->has('customers')->has('userTags'),
    );
});

// ---------- getPaginated
it('getPaginated: returns paginator', function () {
    $paginator = new LengthAwarePaginator(collect([['id' => 1, 'email' => 'a@example.com']]), 1, 10, 1);

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) use ($paginator) {
            $m->shouldReceive('getPaginatedCustomerData')->once()->andReturn($paginator);
        },
    );

    $res = get(
        route('customers.paginated', [
            'page' => 0,
            'per_page' => 10,
            'sort_by' => null,
            'sort_direction' => null,
            'filters' => json_encode([]),
            'tags' => json_encode([]),
        ]),
    );

    $res->assertOk()->assertJsonFragment(['total' => 1]);
});

// ---------- create
it('create: renders inertia form', function () {
    $res = get(route('customers.create'));
    $res->assertOk()->assertInertia(fn(Assert $page) => $page->component('Customer/Create'));
});

// ---------- store
it('store: repository stores and redirects with flash', function () {
    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) {
            $m->shouldReceive('store')->once()->andReturnTrue();
        },
    );

    $payload = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+56934567890',
        'language' => 'en',
        'gender' => 'M',
        'country' => 'US',
        'address_first' => '123 Main St',
        'city' => 'Anytown',
        'postal_code' => '12345',
        'emergency_c_name' => 'Jane Doe',
        'emergency_c_phone' => '+56909876543',
        'citizenship' => 'US',
        'dob' => '1990-01-01',
        'email' => 'john@example.com',
    ];

    $res = post(route('customers.store'), $payload);

    $res->assertRedirect(route('customers.index'))->assertSessionHas('flash', 'Customer created successfully.');
});

it('store: redirects error on exception', function () {
    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) {
            $m->shouldReceive('store')->once()->andThrow(new Exception('boom'));
        },
    );
    expect(auth()->user())->not->toBeNull();
    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->can('CreateCustomers'))->toBeTrue();

    $route = app('router')->getRoutes()->getByName('customers.store');

    $payload = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+56934567890',
        'language' => 'en',
        'gender' => 'M',
        'country' => 'US',
        'address_first' => '123 Main St',
        'city' => 'Anytown',
        'postal_code' => '12345',
        'emergency_c_name' => 'Jane Doe',
        'emergency_c_phone' => '+56909876543',
        'citizenship' => 'US',
        'dob' => '1990-01-01',
        'email' => 'john@example.com',
    ];
    $res = post(route('customers.store'), $payload);
    $res->assertStatus(302)
        ->assertRedirect(route('customers.index'))
        ->assertSessionHas('error', 'Problem creating customer.');
});

// ---------- edit
it('edit: renders inertia with customer and bookings', function () {
    $customer = User::factory()->create();

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) {
            $m->shouldReceive('getBookingDataForCustomer')->once()->andReturn(collect([]));
        },
    );

    $res = get(route('customers.edit', $customer));

    $res->assertOk()->assertInertia(
        fn(Assert $page) => $page->component('Customer/Edit')->has('customer')->has('bookings'),
    );
});

// ---------- update
it('update: calls service and redirects success', function () {
    $customer = User::factory()->create();

    [, $svc] = bindRepoService(
        svcMocker: function (MockInterface $m) use ($customer) {
            $m->shouldReceive('updateCustomer')->once()->andReturnTrue();
        },
    );

    $payload = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+56934567890',
        'language' => 'en',
        'gender' => 'M',
        'country' => 'US',
        'address_first' => '123 Main St',
        'city' => 'Anytown',
        'postal_code' => '12345',
        'emergency_c_name' => 'Jane Doe',
        'emergency_c_phone' => '+56909876543',
        'citizenship' => 'US',
        'dob' => '1990-01-01',
        'email' => 'john@example.com',
        'username' => 'johndoess',
    ];

    $res = put(route('customers.update', ['user' => $customer]), $payload);

    $res->assertStatus(302)
        ->assertRedirect(route('customers.edit', ['user' => $customer]))
        ->assertSessionHas('success', 'Customer updated successfully.');
});

it('update: handles exception and redirects error', function () {
    $customer = User::factory()->create();

    [, $svc] = bindRepoService(
        svcMocker: function (MockInterface $m) {
            $m->shouldReceive('updateCustomer')->once()->andThrow(new Exception('fail'));
        },
    );
    $payload = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+56934567890',
        'language' => 'en',
        'gender' => 'M',
        'country' => 'US',
        'address_first' => '123 Main St',
        'city' => 'Anytown',
        'postal_code' => '12345',
        'emergency_c_name' => 'Jane Doe',
        'emergency_c_phone' => '+56909876543',
        'citizenship' => 'US',
        'dob' => '1990-01-01',
        'email' => 'john@example.com',
    ];
    $res = put(route('customers.update', $customer), $payload);

    $res->assertRedirect(route('customers.edit', $customer->id))->assertSessionHas(
        'error',
        'Problem updating customer.',
    );
});

// ---------- show
it('show: renders inertia View with props', function () {
    $customer = User::factory()->create();

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) {
            $m->shouldReceive('getBookingDataForCustomer')->once()->andReturn(collect([]));
        },
    );

    $res = get(route('customers.show', $customer));

    $res->assertOk()->assertInertia(
        fn(Assert $page) => $page
            ->component('Customer/View')
            ->has('customer')
            ->has('bookings')
            ->has('availableTags')
            ->has('isTemporaryPassword'),
    );
});

// ---------- destroy
it('destroy: deletes via repo and redirects success', function () {
    $customer = User::factory()->create();

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) {
            $m->shouldReceive('delete')->once()->andReturnTrue();
        },
    );

    $res = httpDelete(route('customers.destroy', $customer));
    $res->assertRedirect(route('customers.index'))->assertSessionHas('success', 'Customer deleted successfully.');
});

// ---------- editBySurvivorNumber
it('editBySurvivorNumber: goes to edit when SN exists', function () {
    $customer = User::factory()->create();
    SurvivorNumber::query()->create([
        'user_id' => $customer->id,
        'survivor_number' => '987654321',
    ]);

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) {
            $m->shouldReceive('getBookingDataForCustomer')->andReturn(collect([]));
        },
    );

    $res = get(route('customers.editBySurvivorNumber', '987654321'));
    //dd($res->getContent());
    $res->assertOk()->assertInertia(fn(Assert $page) => $page->component('Customer/Edit'));
});

// ---------- addComment
it('addComment: adds comment and redirects success', function () {
    $customer = User::factory()->create();

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) use ($customer) {
            $m->shouldReceive('find')->once()->andReturn($customer);
            $m->shouldReceive('addComment')->once()->andReturnTrue();
        },
    );

    $res = post(route('customers.addComment', ['user' => $customer->id]), [
        'comment' => 'Nice one',
    ]);

    $res->assertRedirect(route('customers.show', ['user' => $customer->id]))->assertSessionHas(
        'success',
        'Comment added successfully.',
    );
});

it('addComment: redirects error when repo fails', function () {
    $customer = User::factory()->create();

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) use ($customer) {
            $m->shouldReceive('find')->once()->andReturn($customer);
            $m->shouldReceive('addComment')->once()->andReturnFalse();
        },
    );

    $res = post(route('customers.addComment', ['user' => $customer->id]), [
        'comment' => 'meh',
    ]);

    $res->assertRedirect(route('customers.show', ['user' => $customer->id]))->assertSessionHas(
        'error',
        'Failed to add comment.',
    );
});

// ---------- updateTags
it('updateTags: updates tags and redirects success', function () {
    $customer = User::factory()->create();

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) {
            $m->shouldReceive('addTags')->once()->andReturnTrue();
        },
    );

    $res = post(route('customers.updateTags', ['user' => $customer->id]), [
        'tags' => ['vip', 'loyal'],
    ]);

    $res->assertRedirect(route('customers.show', ['user' => $customer->id]))->assertSessionHas(
        'success',
        'Tags updated successfully.',
    );
});

it('updateTags: redirects error when repo fails', function () {
    $customer = User::factory()->create();

    [$repo] = bindRepoService(
        repoMocker: function (MockInterface $m) {
            $m->shouldReceive('addTags')->once()->andReturnFalse();
        },
    );

    $res = post(route('customers.updateTags', ['user' => $customer->id]), [
        'tags' => ['x'],
    ]);

    $res->assertRedirect(route('customers.show', ['user' => $customer->id]))->assertSessionHas(
        'error',
        'Failed to update tags.',
    );
});

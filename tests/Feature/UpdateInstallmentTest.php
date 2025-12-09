<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Http\Controllers\InstallmentController;
use App\Models\Booking;
use App\Models\Cabin;
use App\Models\CabinType;
use App\Models\Customer;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\Support\ActsAsAgent;
use Spatie\Permission\PermissionRegistrar;


uses(ActsAsAgent::class);

uses(DatabaseTransactions::class);

function createMonthlyInstallmentsForPassenger(
    $passengerId,
    $startDate,
    int $count,
    float $amount,
    string $type = 'PAYMENT'
) {
    $start = $startDate instanceof Carbon ? $startDate->startOfDay() : Carbon::parse($startDate)->startOfDay();

    $created = collect();

    for ($i = 0; $i < $count; $i++) {
        $due = (clone $start)->addMonthsNoOverflow($i);

        $inst = Installment::create([
            'passenger_id' => $passengerId,
            'due_date' => $due->format('Y-m-d'),
            'type' => $type,
            'amount' => $amount,
        ]);

        $created->push($inst);
    }
    Log::info('Created installments:', $created->toArray());

    return $created;
}

function getCabin(): array
{
    $ct = CabinType::first() ?: CabinType::create(['cabin_type' => 'Private Cabin']);
    $cabin =
        Cabin::first() ?:
        Cabin::create([
            'cabin_type_id' => $ct->id,
            'cabin_category_id' => 1,
            'cabin_number' => '101',
            'status' => 'AVAILABLE',
        ]);
    return [$ct, $cabin];
}

beforeEach(function () {
    /** @var \App\Models\Booking $this->booking */
    /** @var \Tests\Support\ActsAsAgent $this */
    $this->loginManager();

    [$cabinType, $cabin] = getCabin();
    $customer = Customer::create([
        'username' => Str::uuid(),
        'email' => Str::uuid() . '@customer.dev',
        'password' => bcrypt('testing123'),
    ]);

    $this->booking = Booking::create([
        'event_id' => 1,
        'status' => 'NEW',
        'booking_code' => 'BK-' . Str::upper(Str::random(6)),
        'booking_request_id' => 'REQ-' . Str::upper(Str::random(4)),
        'cabin_id' => $cabin->id,
        'customer_id' => $customer->id,
        'agent_id' => null,
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDay(),
    ]);

    $passenger0 = $this->booking->passengers()->create([
        'first_name' => 'Tony',
        'last_name' => 'Smith',
        'email' => 'tony@example.com',
        'phone' => '123456',
        'survivor_number' => 'S123',
        'passenger_order' => 1,
        'lead_passenger' => true,
        'passenger_allocated_cost' => 500.0,
        'passenger_balance' => 500.0,
    ]);

    $passenger1 = $this->booking->passengers()->create([
        'first_name' => 'Alice',
        'last_name' => 'Doe',
        'email' => 'alice@example.com',
        'phone' => '123456',
        'survivor_number' => 'S12355',
        'passenger_order' => 2,
        'lead_passenger' => false,
        'passenger_allocated_cost' => 500.0,
        'passenger_balance' => 500.0,
    ]);

    $startDate = Carbon::now()->addMonth()->startOfMonth();
    createMonthlyInstallmentsForPassenger($passenger0->id, $startDate, 2, 166.67);
});

it('allows changing due date when before next installment', function () {
  /** @var \App\Models\Booking $this->booking */
  $passenger = $this->booking->passengers()->first();
  $current = $passenger->installments()->orderBy('due_date')->first();
  $next = $passenger
    ->installments()
    ->where('due_date', '>', $current->due_date)
    ->orderBy('due_date')
    ->first();

  $newDate = Carbon::parse($next->due_date)
    ->subDays(1)
    ->format('Y-m-d');

  $response = $this->patch(action([InstallmentController::class, 'update']), [
    'installment' => $current->id,
    'due_date' => $newDate,
  ]);

  $response->assertStatus(302);
  $response->assertSessionHasNoErrors();
  $current->refresh();
  $this->assertEquals(
    $newDate,
    $current->due_date,
    "The due_date in DB does not match. DB: {$current->due_date} — Expected: {$newDate}"
  );

  $this->assertDatabaseHas('installments', [
    'id' => $current->id,
    'due_date' => $newDate,
  ]);
});

it('fails validation when new due date is after next installment', function () {
  /** @var \App\Models\Booking $this->booking */
  $passenger = $this->booking->passengers()->first();
  $current = $passenger->installments()->orderBy('due_date')->first();
  $next = $passenger
    ->installments()
    ->where('due_date', '>', $current->due_date)
    ->orderBy('due_date')
    ->first();

  $response = $this->patch(action([InstallmentController::class, 'update']), [
    'installment' => $current->id,
    'due_date' => Carbon::parse($next->due_date)
      ->addDays(5)
      ->format('Y-m-d'),
  ]);

  $response->assertStatus(302);
  $response->assertSessionHasErrors('due_date');
  $this->assertDatabaseHas('installments', [
    'id' => $current->id,
    'due_date' => $current->due_date,
  ]);
});

it('agent cannot edit an installment', function () {
    /** @var \Tests\TestCase $this */                
    /** @mixin \Tests\Support\ActsAsAgent */        
    /** @var \App\Models\Booking $this->booking */
    
    $this->loginAgent();
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    /** @var \App\Models\Booking $this->booking */
    $passenger = $this->booking->passengers()->first();
    $current = $passenger->installments()->orderBy('due_date')->first();
    $newDate = Carbon::parse($current->due_date)->subDays(1)->format('Y-m-d');

    $response = $this->patch(action([InstallmentController::class, 'update']), [
        'installment' => $current->id,
        'due_date' => $newDate,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('error');

    $current->refresh();
    $this->assertNotEquals($newDate, $current->due_date);
});

it('manager can edit an installment', function () {
    /** @var \Tests\TestCase $this */                
    /** @mixin \Tests\Support\ActsAsAgent */        
    /** @var \App\Models\Booking $this->booking */
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->loginManager();
    /** @var \App\Models\Booking $this->booking */
    $passenger = $this->booking->passengers()->first();
    $current = $passenger->installments()->orderBy('due_date')->first();
    $newDate = Carbon::parse($current->due_date)->subDays(1)->format('Y-m-d');

    $response = $this->patch(action([InstallmentController::class, 'update']), [
        'installment' => $current->id,
        'due_date' => $newDate,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    $current->refresh();
    $this->assertEquals(
        Carbon::parse($newDate)->format('Y-m-d'),
        Carbon::parse($current->due_date)->format('Y-m-d')
    );
});

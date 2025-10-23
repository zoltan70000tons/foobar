<?php

use App\Models\Log;
use App\Models\User;
use App\Models\Booking;
use App\Models\Cabin;
use Database\Seeders\BookingSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;

uses(DatabaseTransactions::class);

it('creates a log for a seeded booking and loads morph + actor relations', function () {

    $this->seed(BookingSeeder::class);

    /** @var Booking $booking */
    $booking = Booking::query()->first();
    /** @var User $user */
    $user = User::query()->first();

    expect($booking)->not->toBeNull();
    expect($user)->not->toBeNull();

    // create log
    /** @var Log $log */
    $log = Log::create([
        'id'           => (string) Str::uuid(),
        'action'       => 'BOOKING_CREATED',
        'actor_type'   => 'system',
        'actor_id'     => $user->id,
        'related_type' => 'booking',
        'related_id'   => $booking->id,
        'description'  => 'Booking created via seeder',
        'payload'      => ['source' => 'seeder', 'flag' => true],
        'created_at'   => now(),
    ]);

    expect($log->exists)->toBeTrue();
    expect($log->id)->toBeString();

    expect($log->payload)->toBeArray()
        ->and($log->payload['source'])->toBe('seeder');
    expect($log->created_at)->toBeInstanceOf(Carbon::class);

    $log->load('actor', 'related');

    expect($log->actor)->not->toBeNull()
        ->and($log->actor->id)->toBe($user->id);

    expect($log->related)->not->toBeNull()
        ->and($log->related->id)->toBe($booking->id);
});



it('supports morphMap "booking" if configured', function () {
    $this->seed(BookingSeeder::class);

    $booking = Booking::first();
    $user    = User::first();

    Relation::enforceMorphMap([
        'booking' => Booking::class,
    ]);

    /** @var Log $log */
    $log = Log::create([
        'id'           => (string) Str::uuid(),
        'action'       => 'WITH_MORPH_MAP',
        'actor_type'   => 'agent',
        'actor_id'     => $user->id,
        'related_type' => 'booking',
        'related_id'   => $booking->id,
        'description'  => 'Using morphMap',
        'payload'      => ['ok' => true],
        'created_at'   => now(),
    ]);

    $log->load('related');

    expect($log->related)->toBeInstanceOf(Booking::class)
        ->and($log->related->id)->toBe($booking->id);
});


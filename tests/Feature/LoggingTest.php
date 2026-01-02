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

it('creates a BOOKING_CREATED log automatically when booking is created', function () {
    Relation::enforceMorphMap([
        'booking' => Booking::class,
        'user' => User::class,
        'cabin' => Cabin::class,
    ]);

    $this->seed(BookingSeeder::class);

    /** @var Booking $booking */
    $booking = Booking::first();
    /** @var User $user */
    $user = User::first();

    expect($booking)->not->toBeNull();
    expect($user)->not->toBeNull();

    $log = Log::where('related_type', 'booking')
        ->where('related_id', $booking->id)
        ->where('action', 'BOOKING_CREATED')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('BOOKING_CREATED');
    expect($log->related_id)->toBe((string) $booking->id);
    expect($log->payload)->toBeArray();
});

it('creates a BOOKING_STATUS_CHANGED log automatically when booking status changes', function () {
    Relation::enforceMorphMap([
        'booking' => Booking::class,
        'user' => User::class,
        'cabin' => Cabin::class,
    ]);
    $this->seed(BookingSeeder::class);

    $booking = Booking::first();
    $user = User::first();

    expect($booking)->not->toBeNull();
    expect($user)->not->toBeNull();

    $booking->status = 'ON HOLD';
    $booking->save();

    $log = Log::where('related_type', 'booking')
        ->where('related_id', $booking->id)
        ->where('action', 'STATUS_CHANGED')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('STATUS_CHANGED');
    expect($log->related_id)->toBe((string) $booking->id);
    expect($log->payload)->toBeArray();
});

it('creates a BOOKING_CANCELLED log automatically when booking is cancelled', function () {
    Relation::enforceMorphMap([
        'booking' => Booking::class,
        'user' => User::class,
        'cabin' => Cabin::class,
    ]);
    $this->seed(BookingSeeder::class);

    $booking = Booking::first();
    $user = User::first();

    expect($booking)->not->toBeNull();
    expect($user)->not->toBeNull();

    $booking->status = 'CANCELLED';
    $booking->save();

    $log = Log::where('related_type', 'booking')
        ->where('related_id', $booking->id)
        ->where('action', 'BOOKING_CANCELLED')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('BOOKING_CANCELLED');
    expect($log->related_id)->toBe((string) $booking->id);
    expect($log->payload)->toBeArray();
});

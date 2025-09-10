<?php

declare(strict_types=1);

namespace App\Jobs;

// Dummy/spies for the helper functions (createTag, attachTag, removeTag).
// Since these are in the same namespace as the Job, PHP will use them
// instead of the global ones during tests.
class _TagDummy
{
    public function __construct(public string $id) {}
}

final class _HelpersSpy
{
    public static array $attachTagCalls = [];
    public static array $removeTagCalls = [];
    public static array $createTagCalls = [];
    public static function reset(): void
    {
        self::$attachTagCalls = [];
        self::$removeTagCalls = [];
        self::$createTagCalls = [];
    }
}

function attachTag($model, string $name, string $type, ?string $color = null, ?string $description = null)
{
    _HelpersSpy::$attachTagCalls[] = compact('model', 'name', 'type', 'color', 'description');
    return new _TagDummy('missing-info-tag-id');
}

function removeTag($model, string $name, string $type)
{
    _HelpersSpy::$removeTagCalls[] = compact('model', 'name', 'type');
    return true;
}

function createTag(string $name, string $type, ?string $color = null, ?string $description = null)
{
    _HelpersSpy::$createTagCalls[] = compact('name', 'type', 'color', 'description');
    return new _TagDummy(match (strtoupper($name)) {
        'OVERDUE'     => 'overdue-tag-id',
        default       => 'tag-id-' . strtolower($name),
    });
}

namespace Tests\Feature\Jobs;

use App\Jobs\AutoTagBookingJob;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery as m;
use App\Models\Booking;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Freeze "now" to make overdue calculations deterministic
    Carbon::setTestNow(Carbon::create(2025, 9, 1, 12, 0, 0, 'America/Los_Angeles'));

    // Reset spies before every test
    \App\Jobs\_HelpersSpy::reset();
});

/**
 * Helpers to build fake passengers/cabin objects
 */
function makePassenger(array $attrs): object
{
    return new class($attrs) {
        public $first_name;
        public $last_name;
        public $dob;
        public $empty_seat;
        private $status;
        public function __construct($a)
        {
            $this->first_name = $a['first_name'] ?? 'John';
            $this->last_name  = $a['last_name']  ?? 'Doe';
            $this->dob        = $a['dob']        ?? '1990-01-01';
            $this->empty_seat = $a['empty_seat'] ?? false;
            $this->status     = $a['installment_status'] ?? [];
        }
        public function getInstallmentStatus()
        {
            return $this->status;
        }
    };
}

function makeCabin(int $cabinTypeId): object
{
    return new class($cabinTypeId) {
        public $cabinType;
        public function __construct($id)
        {
            $this->cabinType = (object)['id' => $id];
        }
    };
}

it('attaches OVERDUE tag when an installment is more than 48h overdue', function () {
    $overdueDate = Carbon::now('America/Los_Angeles')->subDays(3)->toDateString();
    $p1 = makePassenger([
        'installment_status' => ['next_installment' => ['due_date' => $overdueDate]],
    ]);

    $booking = m::mock(Booking::class)->makePartial();
    $booking->passengers = [$p1];
    $booking->cabin = makeCabin(1);
    $booking->tags = [];

    $booking->shouldReceive('attachTags')
        ->once()
        ->with(['overdue-tag-id']);
    $booking->shouldReceive('detachTags')->never();

    (new AutoTagBookingJob($booking))->handle();
});

it('detaches OVERDUE tag when installments are not overdue', function () {
    $futureDate = Carbon::now('America/Los_Angeles')->addDays(5)->toDateString();
    $p1 = makePassenger([
        'installment_status' => ['next_installment' => ['due_date' => $futureDate]],
    ]);

    $booking = m::mock(Booking::class)->makePartial();
    $booking->passengers = [$p1];
    $booking->cabin = makeCabin(1);
    $booking->tags = [];

    $booking->shouldReceive('detachTags')
        ->once()
        ->with(['overdue-tag-id']);
    $booking->shouldReceive('attachTags')->never();

    (new AutoTagBookingJob($booking))->handle();
});

it('adds MISSING INFO tag when passenger lacks info in a private cabin with occupied seat', function () {
    $p1 = makePassenger([
        'first_name' => '',
        'last_name'  => 'Perez',
        'dob'        => '1995-02-02',
        'empty_seat' => false,
    ]);

    $booking = m::mock(Booking::class)->makePartial();
    $booking->passengers = [$p1];
    $booking->cabin = makeCabin(1);
    $booking->tags = [];
    $booking->shouldReceive('attachTags')->zeroOrMoreTimes()->andReturnNull();
    $booking->shouldReceive('detachTags')->zeroOrMoreTimes()->andReturn(0);


    (new AutoTagBookingJob($booking))->handle();

    $calls = \App\Jobs\_HelpersSpy::$attachTagCalls;
    expect($calls)->toHaveCount(1);
    expect($calls[0]['model'])->toBe($booking);
    expect($calls[0]['name'])->toBe('MISSING INFO');
    expect($calls[0]['type'])->toBe('BOOKING');
});

it('removes MISSING INFO tag when passenger is missing info but seat is not occupied', function () {
    $p1 = makePassenger([
        'first_name' => '',
        'last_name'  => 'Perez',
        'dob'        => '1995-02-02',
        'empty_seat' => true, // seat is empty -> should not trigger
    ]);

    $booking = m::mock(Booking::class)->makePartial();
    $booking->passengers = [$p1];
    $booking->cabin = makeCabin(1);
    $booking->tags = [];
    $booking->shouldReceive('attachTags')->zeroOrMoreTimes()->andReturnNull();
    $booking->shouldReceive('detachTags')->zeroOrMoreTimes()->andReturn(0);


    (new AutoTagBookingJob($booking))->handle();

    $calls = \App\Jobs\_HelpersSpy::$removeTagCalls;
    expect($calls)->toHaveCount(1);
    expect($calls[0]['model'])->toBe($booking);
    expect($calls[0]['name'])->toBe('MISSING INFO');
    expect($calls[0]['type'])->toBe('BOOKING');
});

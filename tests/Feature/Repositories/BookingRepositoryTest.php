<?php

use App\Helpers\InstallmentHelper;
use App\Models\Booking;
use App\Models\Cabin;
use App\Models\CabinType;
use App\Models\Customer;
use App\Models\User;
use App\Models\Tag;
use App\Models\UserDetail;
use App\Repositories\AdjustmentsRepository;
use App\Repositories\BookingRepository;
use App\Repositories\PassengerRepository;
use App\Services\PaymentInfoService;
use App\Services\PaymentService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function makeDeps(): array {
    return [
        'passengerRepository' => \Mockery::mock(PassengerRepository::class),
        'adjustmentsRepository' => \Mockery::mock(AdjustmentsRepository::class),
        'paymentService' => \Mockery::mock(PaymentService::class),
        'paymentInfoService' => \Mockery::mock(PaymentInfoService::class),
    ];
}

function makeRepo(): BookingRepository {
    $deps = makeDeps();
    return new BookingRepository(
        $deps['passengerRepository'],
        $deps['adjustmentsRepository'],
        $deps['paymentService'],
        $deps['paymentInfoService'],
    );
}

function ensureCabin(): array {
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

function ensureTagNew(): Tag {
    $tag = Tag::where('type', 'booking')->where('name', 'NEW')->first();
    if (!$tag) {
        $tag = new Tag();
        if (property_exists($tag, 'incrementing') && $tag->incrementing === false) {
            $tag->id = (string) Str::uuid();
        }
        $tag->type = 'booking';
        $tag->name = 'NEW';
        $tag->save();
    }
    return $tag;
}

function bookingWithRels(array $overrides = [], array $passengerData = []): Booking {
    [$cabinType, $cabin] = ensureCabin();

    $customer = Customer::create([
        'username' => $passengerData['username'] ?? Str::uuid(),
        'email' => $passengerData['email'] ?? Str::uuid() . '@customer.dev',
        'password' => bcrypt('testing123'),
    ]);

    UserDetail::updateOrCreate(
        ['user_id' => $customer->id],
        [
            'first_name' => $passengerData['first_name'] ?? 'Dave',
            'last_name' => $passengerData['last_name'] ?? 'Smith',
            'full_name' => ($passengerData['first_name'] ?? 'Dave') . ' ' . ($passengerData['last_name'] ?? 'Smith'),
            'language' => 'en',
        ],
    );

    $agent = User::firstOrCreate(
        ['email' => 'agentx@example.com'],
        ['username' => 'agentx', 'password' => bcrypt('1234')],
    );

    $booking = Booking::create(
        array_merge(
            [
                'event_id' => 1,
                'status' => 'NEW',
                'booking_code' => 'BK-' . Str::upper(Str::random(6)),
                'booking_request_id' => 'REQ-' . Str::upper(Str::random(4)),
                'cabin_id' => $cabin->id,
                'customer_id' => $customer->id,
                'agent_id' => $agent->id,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDay(),
            ],
            $overrides,
        ),
    );

    $booking->passengers()->create([
        'first_name' => $passengerData['p_first_name'] ?? 'Alice',
        'last_name' => $passengerData['p_last_name'] ?? 'Doe',
        'email' => $passengerData['p_email'] ?? 'alice@example.com',
        'phone' => $passengerData['p_phone'] ?? '123456',
        'survivor_number' => $passengerData['p_survivor'] ?? 'S123',
        'passenger_order' => 1,
        'lead_passenger' => true,
        'passenger_allocated_cost' => 500.0,
        'passenger_balance' => 500.0,
    ]);

    $tag = ensureTagNew();

    DB::table('taggings')->insert([
        'entity_id' => (string) $booking->id,
        'entity_type' => 'booking',
        'tag_id' => (string) $tag->id,
        'created_at' => now(),
    ]);

    return $booking->fresh(['cabin.cabinType', 'customer.detail', 'passengers']);
}

beforeEach(function () {
    DB::beginTransaction();
    \Mockery::getConfiguration()->allowMockingNonExistentMethods(true);
    $this->installmentHelper = \Mockery::mock('alias:' . InstallmentHelper::class);
});

afterEach(function () {
    \Mockery::close();
    DB::rollBack();
});

it('filter by event_id and status, supports keyword, tags, user_ids, page and dateRange', function () {
    $match = bookingWithRels(
        ['event_id' => 1, 'status' => 'NEW', 'booking_code' => 'cruise-ok'],
        ['first_name' => 'Cruise', 'last_name' => 'Ok', 'email' => ''],
    );

    bookingWithRels(
        ['event_id' => 1, 'status' => 'NEW', 'booking_code' => 'NOPE-001', 'booking_request_id' => 'not-ok'],
        ['first_name' => 'Alice', 'last_name' => 'Wonder', 'email' => 'alice@customer.dev'],
    );

    bookingWithRels(
        [
            'event_id' => 1,
            'status' => 'CANCELLED',
            'booking_code' => 'NOPE-002',
            'booking_request_id' => 'Not-ok-again',
        ],
        ['first_name' => 'Bob', 'last_name' => 'Builder', 'email' => 'bob@customer.dev'],
    );

    $this->installmentHelper
        ->shouldReceive('getFirstUnpaidInstallmentForBooking')
        ->andReturnUsing(function ($booking) use ($match) {
            return $booking->id === $match->id ? now()->addDays(15)->toDateString() : null;
        });

    $repo = makeRepo();
    request()->merge(['page' => 1]);

    $tag = ensureTagNew();

    /** @var LengthAwarePaginator $res */
    $resStatus = $repo->getByStatus(
        eventId: 1,
        status: 'NEW',
        keyword: 'cruise-ok',
        perPage: 10,
        sortKey: 'created_at',
        sortDirection: 'desc',
        tags: [$tag->id],
        user_ids: [$match->agent_id],
        dateRange: [
            'longestDueDateInstallment' => [
                'startDate' => now()->addDay()->toDateString(),
                'endDate' => now()->addMonth()->toDateString(),
            ],
        ],
    );

    expect($resStatus)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($resStatus->total())->toBe(1);
    $only = $resStatus->items()[0];
    expect($only->fullName)
        ->not()
        ->toBeNull();
    expect($only->cabinType)
        ->not()
        ->toBeNull();
    expect($only->longestDueDateInstallment)->toEqual(now()->addDays(15)->toDateString());
    expect($only->subRows)->toHaveCount(1);
    expect($only->subRows[0]->first_name)->toEqual('ALICE');
});

it('searches by booking_code in keyword', function () {
    $match = bookingWithRels(
        ['event_id' => 1, 'status' => 'NEW', 'booking_code' => 'BK-LOOKUP-999'],
        ['first_name' => 'X', 'last_name' => 'Y'],
    );

    bookingWithRels(['event_id' => 1, 'status' => 'NEW', 'booking_code' => 'BK-OTHER-123']);
    bookingWithRels(['event_id' => 1, 'status' => 'CANCELLED', 'booking_code' => 'BK-OTHER-456']);

    $this->installmentHelper->shouldReceive('getFirstUnpaidInstallmentForBooking')->andReturn(null);

    $repo = makeRepo();
    request()->merge(['page' => 1]);
    $tag = ensureTagNew();

    /** @var LengthAwarePaginator $res */
    $res = $repo->getByStatus(
        eventId: 1,
        status: 'NEW',
        keyword: 'lookup-999',
        perPage: 10,
        sortKey: 'created_at',
        sortDirection: 'desc',
        tags: [$tag->id],
        user_ids: [$match->agent_id],
    );

    expect($res->total())->toBe(1);
    $only = $res->items()[0];
    expect($only->booking_code)->toEqual('BK-LOOKUP-999');
});

it('searches by passenger phone in keyword', function () {
    $match = bookingWithRels(
        ['event_id' => 1, 'status' => 'NEW', 'booking_code' => 'BK-PHONE-001'],
        ['p_phone' => '555777999'],
    );

    bookingWithRels(['event_id' => 1, 'status' => 'NEW'], ['p_phone' => '111222333']);
    bookingWithRels(['event_id' => 1, 'status' => 'CANCELLED'], ['p_phone' => '444555666']);

    $this->installmentHelper->shouldReceive('getFirstUnpaidInstallmentForBooking')->andReturn(null);

    $repo = makeRepo();
    request()->merge(['page' => 1]);
    $tag = ensureTagNew();

    /** @var LengthAwarePaginator $res */
    $res = $repo->getByStatus(
        eventId: 1,
        status: 'NEW',
        keyword: '7779',
        perPage: 10,
        sortKey: 'created_at',
        sortDirection: 'desc',
        tags: [$tag->id],
        user_ids: [$match->agent_id],
    );

    expect($res->total())->toBe(1);
    $only = $res->items()[0];
    expect($only->booking_code)->toEqual('BK-PHONE-001');
});

it('searches by customer email in keyword', function () {
    $email = 'customer.findme@dev.test';
    $match = bookingWithRels(
        ['event_id' => 1, 'status' => 'NEW', 'booking_code' => 'BK-CUSTEMAIL-001'],
        ['p_email' => $email, 'p_first_name' => 'Foo', 'p_last_name' => 'Bar'],
    );

    bookingWithRels(['event_id' => 1, 'status' => 'NEW'], ['email' => 'other@domain.test']);
    bookingWithRels(['event_id' => 1, 'status' => 'CANCELLED'], ['email' => 'nope@domain.test']);

    $this->installmentHelper->shouldReceive('getFirstUnpaidInstallmentForBooking')->andReturn(null);

    $repo = makeRepo();
    request()->merge(['page' => 1]);
    $tag = ensureTagNew();

    /** @var LengthAwarePaginator $res */
    $res = $repo->getByStatus(
        eventId: 1,
        status: 'NEW',
        keyword: $email,
        perPage: 10,
        sortKey: 'created_at',
        sortDirection: 'desc',
        tags: [],
        user_ids: [],
    );

    expect($res->total())->toBe(1);
    $only = $res->items()[0];
    expect($only->booking_code)->toEqual('BK-CUSTEMAIL-001');
});

it('searches by booking_request_id in keyword', function () {
    $match = bookingWithRels(
        [
            'event_id' => 1,
            'status' => 'NEW',
            'booking_code' => 'BK-REQ-001',
            'booking_request_id' => 'REQ-SEARCH-999',
        ],
        ['first_name' => 'Nick', 'last_name' => 'Request'],
    );

    bookingWithRels([
        'event_id' => 1,
        'status' => 'NEW',
        'booking_code' => 'BK-NOPE-001',
        'booking_request_id' => 'REQ-NOPE-001',
    ]);

    bookingWithRels([
        'event_id' => 1,
        'status' => 'CANCELLED',
        'booking_code' => 'BK-NOPE-002',
        'booking_request_id' => 'REQ-NOPE-002',
    ]);

    $this->installmentHelper->shouldReceive('getFirstUnpaidInstallmentForBooking')->andReturn(null);

    $repo = makeRepo();
    request()->merge(['page' => 1]);
    $tag = ensureTagNew();

    /** @var LengthAwarePaginator $res */
    $res = $repo->getByStatus(
        eventId: 1,
        status: 'NEW',
        keyword: 'req-search-999',
        perPage: 10,
        sortKey: 'created_at',
        sortDirection: 'desc',
        tags: [$tag->id],
        user_ids: [$match->agent_id],
    );

    expect($res->total())->toBe(1);
    $only = $res->items()[0];
    expect($only->booking_request_id)->toEqual('REQ-SEARCH-999');
});

<?php

namespace Tests\Unit\Repository\BookingRepository;

use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Models\CabinCategorySpec;
use App\Models\CabinSpec;
use App\Models\CabinType;
use App\Models\Organization;
use App\Models\TemporaryReservation;
use App\Models\User;
use App\Repositories\BookingRepository;
use Tests\Unit\utils\CabinTypeSeeder;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CabinTypeSeeder::class);

    $this->passengerRepository = Mockery::mock('App\Repositories\PassengerRepository');
    $this->adjustmentsRepository = Mockery::mock('App\Repositories\AdjustmentsRepository');
    $this->paymentService = Mockery::mock('App\Services\PaymentService');
    $this->paymentInfoService = Mockery::mock('App\Services\PaymentInfoService');
    $this->bookingRepository = new BookingRepository(
        $this->passengerRepository,
        $this->adjustmentsRepository,
        $this->paymentService,
        $this->paymentInfoService,
    );
});

describe('getBookableCabinByParamsTest', function () {
    it('returns a cabin that matches the given parameters', function () {
        $cabinCategorySpec = CabinCategorySpec::factory()->create([
            'capacity' => 4,
        ]);
        $cabinCategory = CabinCategory::factory([
            'cabin_category_spec_id' => $cabinCategorySpec->id,
        ])->create();

        $type = CabinType::query()->where('cabin_type', 'Private Cabin')->firstOrFail();

        $cabinSpec = CabinSpec::factory([
            'cabin_number' => 3456,
            'deck' => 9,
            'total_berths' => 4,
            'lower_bed_type_1' => '2C',
            'lower_bed_type_2' => '4B',
            'upper_berths' => 2,
            'accessible' => false,
            'connects_with' => 1808,
            'location' => 'AF',
            'balcony' => true,
            'obstructed_view' => false,
        ])->create();

        $cabin = Cabin::factory()
            ->state([
                'status' => 'AVAILABLE',
                'cabin_spec_id' => $cabinSpec->id,
                'cabin_type_id' => $type->id,
                'cabin_category_id' => $cabinCategory->id,
            ])
            ->create();

        $result = app(BookingRepository::class)->getBookableCabinByParams(4, '3456', $type->id, $cabinCategory->id);

        expect($result)
            ->not()
            ->toBeNull()
            ->and($result->id)
            ->toBe($cabin->id);
    });

    it('does not return cabins with active temporary reservations', function () {
        $org = Organization::factory()->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();
        $category = CabinCategory::factory()
            ->has(CabinCategorySpec::factory(['capacity' => 2]), 'spec')
            ->create();

        $type = CabinType::query()->where('cabin_type', 'Private Cabin')->firstOrFail();

        $cabin = Cabin::factory()
            ->for($category, 'category')
            ->for($type, 'cabinType')
            ->has(CabinSpec::factory(['cabin_number' => '2345']), 'cabinSpec')
            ->state(['status' => 'AVAILABLE'])
            ->create();

        TemporaryReservation::factory([
            'cabin_id' => $cabin->id,
            'user_id' => $user->id,
            'expires_at' => now()->addHour(),
        ])->create();

        $result = app(BookingRepository::class)->getBookableCabinByParams(2, '2345', $type->id, $category->id);

        expect($result)->toBeNull();
    });

    it('ignores cabins with non-bookable status', function () {
        $category = CabinCategory::factory()
            ->has(CabinCategorySpec::factory(['capacity' => 3]), 'spec')
            ->create();

        $type = CabinType::query()->where('cabin_type', 'Private Cabin')->firstOrFail();

        Cabin::factory()
            ->for($category, 'category')
            ->for($type, 'cabinType')
            ->has(CabinSpec::factory(['cabin_number' => '1234']), 'cabinSpec')
            ->state(['status' => 'BOOKED']) // not in allowed list
            ->create();

        $result = app(BookingRepository::class)->getBookableCabinByParams(3, '1234', $type->id, $category->id);

        expect($result)->toBeNull();
    });

    it('does not return a cabin if the capacity does not match', function () {
        $category = CabinCategory::factory()
            ->has(CabinCategorySpec::factory(['capacity' => 5]), 'spec')
            ->create();

        $type = CabinType::query()->where('cabin_type', 'Private Cabin')->firstOrFail();

        Cabin::factory()
            ->for($category, 'category')
            ->for($type, 'cabinType')
            ->has(CabinSpec::factory(['cabin_number' => '9876']), 'cabinSpec')
            ->state(['status' => 'AVAILABLE'])
            ->create();

        // Search with wrong capacity
        $result = app(BookingRepository::class)->getBookableCabinByParams(
            4, // <-- mismatch
            '9876',
            $type->id,
            $category->id,
        );

        expect($result)->toBeNull();
    });

    it('does not return a cabin if the cabinCategoryId does not match', function () {
        $category = CabinCategory::factory()
            ->has(CabinCategorySpec::factory(['capacity' => 2]), 'spec')
            ->create();

        $wrongCategory = CabinCategory::factory()
            ->has(CabinCategorySpec::factory(['capacity' => 2]), 'spec')
            ->create();

        $type = CabinType::query()->where('cabin_type', 'Private Cabin')->firstOrFail();

        Cabin::factory()
            ->for($category, 'category')
            ->for($type, 'cabinType')
            ->has(CabinSpec::factory(['cabin_number' => '8765']), 'cabinSpec')
            ->state(['status' => 'AVAILABLE'])
            ->create();

        // Search with wrong category id
        $result = app(BookingRepository::class)->getBookableCabinByParams(
            2,
            '8765',
            $type->id,
            $wrongCategory->id, // <-- mismatch
        );

        expect($result)->toBeNull();
    });
});

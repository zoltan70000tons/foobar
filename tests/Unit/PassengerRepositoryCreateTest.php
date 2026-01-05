<?php

use App\Models\{Booking, Cabin, CabinCategory, CabinCategorySpec, CabinType, Event, Organization, Passenger, User};
use App\Repositories\PassengerRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Unit\utils\CabinTypeSeeder;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CabinTypeSeeder::class);

    $this->paymentService = Mockery::mock('App\Services\PaymentService');
    $this->passengerRepository = new PassengerRepository($this->paymentService);
});

describe('Create function', function () {
    it('returns false when no user is authenticated', function () {
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory()->create();

        Auth::login($user);
        $booking = Booking::factory([
            'event_id' => $event->id,
            'booking_code' => 'MOCK_BOOKING_CODE',
            'customer_id' => $user->id,
        ])->create();
        Auth::logout();

        $result = $this->passengerRepository->create([], $booking);
        expect($result)->toBeFalse();
    });

    test('returns false when no cabin assigned', function () {
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory(['organization_id' => $org->id])->create();

        Auth::login($user);
        $booking = Booking::factory([
            'event_id' => $event->id,
            'booking_code' => 'MOCK_BOOKING_CODE',
            'customer_id' => $user->id,
        ])->create();

        $result = $this->passengerRepository->create([], $booking);
        expect($result)->toBeFalse();
    });

    test('returns false when no passenger data provided', function () {
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory(['organization_id' => $org->id])->create();
        $cabin = Cabin::factory()->create();

        Auth::login($user);
        $booking = Booking::factory([
            'event_id' => $event->id,
            'booking_code' => 'MOCK_BOOKING_CODE',
            'customer_id' => $user->id,
            'cabin_id' => $cabin->id,
        ])->create();

        $result = $this->passengerRepository->create([], $booking);
        expect($result)->toBeFalse();
    });

    test('returns false when could not fill additional seats', function () {
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory(['organization_id' => $org->id])->create();
        $cabin = Cabin::factory()->create();

        Auth::login($user);
        $booking = Booking::factory([
            'event_id' => $event->id,
            'booking_code' => 'MOCK_BOOKING_CODE',
            'customer_id' => $user->id,
            'cabin_id' => $cabin->id,
        ])->create();

        $passengerData = [
            'newsletter' => true,
            'email' => $user->email,
            'travel_info' => true,
            'terms_n_cons' => true,
            'cabin_conf_accp' => true,
            'single_t_agreement' => true,
            'number_of_installments' => 4,
        ];

        $result = $this->passengerRepository->create($passengerData, $booking);
        expect($result)->toBeFalse();
    });

    test('returns true when lead and other passengers created', function () {
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory(['organization_id' => $org->id])->create();

        $cabinCategorySpec = CabinCategorySpec::factory(['capacity' => 4])->create();
        $cabinCategory = CabinCategory::factory(['cabin_category_spec_id' => $cabinCategorySpec->id])->create();
        $cabinType = CabinType::find(1) ?? CabinType::factory(['id' => 1, 'cabin_type' => 'Private Cabin'])->create();

        $cabin = Cabin::factory([
            'cabin_category_id' => $cabinCategory->id,
            'cabin_type_id' => $cabinType->id,
        ])->create();

        Auth::login($user);
        $booking = Booking::factory([
            'event_id' => $event->id,
            'booking_code' => 'MOCK_BOOKING_CODE',
            'customer_id' => $user->id,
            'cabin_id' => $cabin->id,
        ])->create();

        $passengerData = [
            'lead_passenger' => true,
            'newsletter' => true,
            'email' => $user->email,
            'travel_info' => true,
            'terms_n_cons' => true,
            'cabin_conf_accp' => true,
            'single_t_agreement' => true,
            'number_of_installments' => 4,
        ];

        $this->paymentService->shouldReceive('createInstallments')->times(3);

        $this->passengerRepository->create($passengerData, $booking);

        expect(
            Passenger::where('booking_id', $booking->id)
                ->where('lead_passenger', true)
                ->where('email', $user->email)
                ->exists(),
        )->toBeTrue();
        expect(
            Passenger::where('booking_id', $booking->id)
                ->where('lead_passenger', false)
                ->count(),
        )->toBe(3);
    });

    test('fill one passenger seat with installments', function () {
        $booking = createBookingWithCabin(capacity: 2);
        $this->paymentService->shouldReceive('createInstallments')->once();

        $this->passengerRepository->fillAditionalSeats(1, $booking->id, 4000, 'CREDIT_CARD', 4);

        expect(
            Passenger::where('booking_id', $booking->id)
                ->where('lead_passenger', false)
                ->count(),
        )->toBe(1);
    });

    test('fill one passenger seat with paid in full', function () {
        $booking = createBookingWithCabin(capacity: 2);
        $this->paymentService->shouldReceive('createInstallments')->never();

        $this->passengerRepository->fillAditionalSeats(1, $booking->id, 4000, 'CREDIT_CARD', false);

        expect(
            Passenger::where('booking_id', $booking->id)
                ->where('lead_passenger', false)
                ->count(),
        )->toBe(1);
    });

    test('fill three passenger seats with paid in full', function () {
        $booking = createBookingWithCabin(capacity: 4);
        $this->paymentService->shouldReceive('createInstallments')->times(3);

        $this->passengerRepository->fillAditionalSeats(3, $booking->id, 4000, 'CREDIT_CARD', 4);

        expect(
            Passenger::where('booking_id', $booking->id)
                ->where('lead_passenger', false)
                ->count(),
        )->toBe(3);
    });

    function createBookingWithCabin(int $capacity): Booking {
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory(['organization_id' => $org->id])->create();

        $cabinCategorySpec = CabinCategorySpec::factory(['capacity' => $capacity])->create();
        $cabinCategory = CabinCategory::factory(['cabin_category_spec_id' => $cabinCategorySpec->id])->create();
        $cabinType = CabinType::find(1) ?? CabinType::factory(['id' => 1, 'cabin_type' => 'Private Cabin'])->create();
        $cabin = Cabin::factory([
            'cabin_category_id' => $cabinCategory->id,
            'cabin_type_id' => $cabinType->id,
        ])->create();

        Auth::login($user);

        return Booking::factory([
            'event_id' => $event->id,
            'booking_code' => 'MOCK_BOOKING_CODE',
            'customer_id' => $user->id,
            'cabin_id' => $cabin->id,
        ])->create();
    }
});

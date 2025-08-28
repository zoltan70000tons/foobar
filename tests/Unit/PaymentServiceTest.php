<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Models\CabinCategorySpec;
use App\Models\CabinType;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Passenger;
use App\Models\User;
use App\Repositories\PassengerRepository;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PassengerRepository $passengerRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\CabinTypeSeeder::class);

        $this->paymentService = Mockery::mock('App\Services\PaymentService');

        $this->passengerRepository = new PassengerRepository(
            $this->paymentService,
        );
    }

    public function test_returns_false_when_no_user_is_authenticated()
    {
        $org = Organization::factory()->create();
        $event = Event::factory([
            'organization_id' => $org->id,
        ])->create();
        $user = User::factory()->create();

        Auth::login($user);

        $booking = Booking::factory([
            'event_id' => $event->id,
            'booking_code' => 'MOCK_BOOKING_CODE',
            'customer_id' => $user->id,
        ])->create();

        Auth::logout();

        $result = $this->passengerRepository->create([], $booking);

        $this->assertFalse($result);
    }

    public function test_returns_false_when_no_cabin_assigned()
    {
        $org = Organization::factory()->create();
        $event = Event::factory([
            'organization_id' => $org->id,
        ])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();

        Auth::login($user);

        $booking = Booking::factory([
            'event_id' => $event->id,
            'booking_code' => 'MOCK_BOOKING_CODE',
            'customer_id' => $user->id,
        ])->create();

        $result = $this->passengerRepository->create([], $booking);

        $this->assertFalse($result);
    }

    public function test_returns_false_when_no_passenger_data_provided()
    {
        $org = Organization::factory()->create();
        $event = Event::factory([
            'organization_id' => $org->id,
        ])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();
        $cabin = Cabin::factory()->create();

        Auth::login($user);

        $booking = Booking::factory([
            'event_id' => $event->id,
            'booking_code' => 'MOCK_BOOKING_CODE',
            'customer_id' => $user->id,
            'cabin_id' => $cabin->id,
        ])->create();

        $result = $this->passengerRepository->create([], $booking);

        $this->assertFalse($result);
    }

    public function test_returns_false_when_could_not_fill_additional_seats()
    {
        $org = Organization::factory()->create();
        $event = Event::factory([
            'organization_id' => $org->id,
        ])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();
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

        $this->assertFalse($result);
    }

    public function test_returns_true_when_lead_and_other_passengers_created()
    {
        $org = Organization::factory()->create();
        $event = Event::factory([
            'organization_id' => $org->id,
        ])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();

        $cabinCategorySpec = CabinCategorySpec::factory([
            'capacity' => 4,
        ])->create();
        $cabinCategory = CabinCategory::factory([
            'cabin_category_spec_id' => $cabinCategorySpec->id,
        ])->create();
        $cabinType = CabinType::find(1) ?? CabinType::factory([
            'id' => 1,
            'cabin_type' => 'Private Cabin',
        ])->create();

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

        $this->paymentService
            ->shouldReceive('createInstallments')
            ->times(3);

        $this->passengerRepository->create($passengerData, $booking);

        $this->assertDatabaseHas('passengers', [
            'booking_id' => $booking->id,
            'lead_passenger' => true,
            'email' => $user->email,
        ]);

        $this->assertEquals(
            3,
            Passenger::where('booking_id', $booking->id)
                ->where('lead_passenger', false)
                ->count()
        );
    }

    public function test_fill_one_passenger_seat_with_installments()
    {
        $org = Organization::factory()->create();
        $event = Event::factory([
            'organization_id' => $org->id,
        ])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();

        $cabinCategorySpec = CabinCategorySpec::factory([
            'capacity' => 2,
        ])->create();
        $cabinCategory = CabinCategory::factory([
            'cabin_category_spec_id' => $cabinCategorySpec->id,
        ])->create();
        $cabinType = CabinType::find(1) ?? CabinType::factory([
            'id' => 1,
            'cabin_type' => 'Private Cabin',
        ])->create();

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

        $this->paymentService
            ->shouldReceive('createInstallments')
            ->times(1);

        $this->passengerRepository->fillAditionalSeats(1, $booking->id, 4000,  'CREDIT_CARD', 4);

        $this->assertEquals(
            1,
            Passenger::where('booking_id', $booking->id)
                ->where('lead_passenger', false)
                ->count()
        );
    }

    public function test_fill_one_passenger_seat_with_paid_in_full()
    {
        $org = Organization::factory()->create();
        $event = Event::factory([
            'organization_id' => $org->id,
        ])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();

        $cabinCategorySpec = CabinCategorySpec::factory([
            'capacity' => 2,
        ])->create();
        $cabinCategory = CabinCategory::factory([
            'cabin_category_spec_id' => $cabinCategorySpec->id,
        ])->create();
        $cabinType = CabinType::find(1) ?? CabinType::factory([
            'id' => 1,
            'cabin_type' => 'Private Cabin',
        ])->create();

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

        $this->paymentService
            ->shouldReceive('createInstallments')
            ->times(0);

        $this->passengerRepository->fillAditionalSeats(1, $booking->id, 4000,  'CREDIT_CARD', false);

        $this->assertEquals(
            1,
            Passenger::where('booking_id', $booking->id)
                ->where('lead_passenger', false)
                ->count()
        );
    }

    public function test_fill_three_passenger_seat_with_paid_in_full()
    {
        $org = Organization::factory()->create();
        $event = Event::factory([
            'organization_id' => $org->id,
        ])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();

        $cabinCategorySpec = CabinCategorySpec::factory([
            'capacity' => 4,
        ])->create();
        $cabinCategory = CabinCategory::factory([
            'cabin_category_spec_id' => $cabinCategorySpec->id,
        ])->create();
        $cabinType = CabinType::find(1) ?? CabinType::factory([
            'id' => 1,
            'cabin_type' => 'Private Cabin',
        ])->create();

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

        $this->paymentService
            ->shouldReceive('createInstallments')
            ->times(3);

        $this->passengerRepository->fillAditionalSeats(3, $booking->id, 4000,  'CREDIT_CARD', 4);

        $this->assertEquals(
            3,
            Passenger::where('booking_id', $booking->id)
                ->where('lead_passenger', false)
                ->count()
        );
    }
}

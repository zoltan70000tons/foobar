<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Cabin;
use App\Models\Event;
use App\Models\Passenger;
use App\Models\SurvivorNumber;
use App\Models\User;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\BookingRepository;

class BookingRepositoryTest extends TestCase {
    use RefreshDatabase;

    protected BookingRepository $bookingRepository;

    protected function setUp(): void {
        parent::setUp();

        $this->seed(\Database\Seeders\CabinTypeSeeder::class);

        $this->passengerRepository = Mockery::mock('App\Repositories\PassengerRepository');
        $this->adjustmentRepository = Mockery::mock('App\Repositories\AdjustmentsRepository');
        $this->paymentService = Mockery::mock('App\Services\PaymentService');
        $this->paymentInfoService = Mockery::mock('App\Services\PaymentInfoService');

        $this->bookingRepository = new BookingRepository(
            $this->passengerRepository,
            $this->adjustmentRepository,
            $this->paymentService,
            $this->paymentInfoService,
        );
    }

    public function test_create_booking_without_cabin_or_temp_id_throws_exception() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You must provide a Cabin object or a Temporary Booking ID.');

        $this->bookingRepository->createBooking([], [], null, null);
    }

    public function test_create_booking_with_invalid_temporary_booking_id() {
        $response = $this->bookingRepository->createBooking([], [], null, 999);

        $this->assertTrue($response['error']);
        $this->assertEquals('Temporary booking ID not found.', $response['message']);
    }

    public function test_create_booking_with_valid_cabin() {
        $cabin = Cabin::factory()->create();
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $survivorNumber = SurvivorNumber::factory()->create([
            'user_id' => $user->id,
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId(1);
        $this->actingAs($user);

        $bookingData = Booking::factory([
            'event_id' => $event->id,
            'customer_id' => $user->id,
            'number_of_installments' => 4,
        ])
            ->make()
            ->toArray();
        $passengerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'survivor_number' => $survivorNumber->survivor_number,
            'payment_plan' => 'INSTALLMENTS',
            'addons' => [['id' => 1], ['id' => 2]],
        ];

        $mockPassenger = Passenger::factory($passengerData)->make();
        $mockPassenger->id = 1;

        $this->bookingRepository->passengerRepository->shouldReceive('create')->once()->andReturn($mockPassenger);

        $this->bookingRepository->paymentService->shouldReceive('createInstallments')->once();

        $this->bookingRepository->adjustmentsRepository->shouldReceive('getAdjustmentsBySurvivorNumber')->andReturn(3);

        $this->bookingRepository->adjustmentsRepository->shouldReceive('attachAdjustments')->once();

        $this->bookingRepository->paymentInfoService->shouldReceive('syncAllocatedCost')->once();

        $response = $this->bookingRepository->createBooking($bookingData, $passengerData, $cabin);

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Booking created successfully.', $response['message']);
        $this->assertArrayHasKey('booking', $response);
        $this->assertArrayHasKey('passenger', $response);

        $this->assertDatabaseHas('bookings', [
            'customer_id' => $user->id,
            'event_id' => $event->id,
            'cabin_id' => $cabin->id,
            'is_single_occupancy' => false,
            'bed_config' => 'SEPARATED',
            'status' => 'NEW',
        ]);
    }
}

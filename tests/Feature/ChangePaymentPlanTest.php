<?php

namespace Tests\Feature;

use App\Models\Adjustment;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Installment;
use App\Models\Passenger;
use App\Repositories\BookingRepository;
use Illuminate\Support\Facades\DB;
use Mockery;
use App\Repositories\PassengerRepository;
use App\Repositories\AdjustmentsRepository;
use App\Services\PaymentService;
use App\Services\PaymentInfoService;
use Carbon\Carbon;
use InvalidArgumentException;

afterEach(function () {
    DB::rollBack();
});

beforeEach(function () {
    DB::beginTransaction();
    $this->passengerRepository = Mockery::mock(PassengerRepository::class);
    $this->adjustmentsRepository = Mockery::mock(AdjustmentsRepository::class);
    $this->paymentService = Mockery::mock(PaymentService::class);
    $this->paymentInfoService = Mockery::mock(PaymentInfoService::class);

    $this->bookingRepository = new BookingRepository(
        $this->passengerRepository,
        $this->adjustmentsRepository,
        $this->paymentService,
        $this->paymentInfoService
    );
});

it('creates installments when converting PAY_IN_FULL to INSTALLMENTS respecting event cutoff', function () {

    $event = Event::first();
    $booking = Booking::where('payment_plan', 'PAY_IN_FULL')->where('event_id', 1)->first();

    if (!$event) {
        throw new InvalidArgumentException('Event with ID 1 not found. Please ensure the test database has an event with ID 1.');
    }

    if (!$booking) {
        throw new InvalidArgumentException('No booking with PAY_IN_FULL found. Please ensure the test database has such a booking.');
    }


    expect($booking)->not->toBeNull();
    expect($booking->payment_plan)->toBe('PAY_IN_FULL');

    // adjustments repo not relevant here
    $this->adjustmentsRepository->shouldReceive('getPaidInFullId')->andReturn(2);
    $this->paymentInfoService->shouldReceive('syncAllocatedCost')->andReturnNull();

    //To test cutoff, we push event start date further into future
    $event->start_date = Carbon::parse($event->start_date)->addMonths(5);
    $event->save();

    $result = $this->bookingRepository->changePaymentPlan($booking, 'INSTALLMENTS', 5);
    expect($result['booking']->payment_plan)->toBe('INSTALLMENTS');

   
    $booking->passengers->each(function (Passenger $passenger) use ($booking, $event){
        $installments = Installment::where('passenger_id', $passenger->id)
            ->where('type', 'PAYMENT')
            ->orderBy('due_date')
            ->get();

        expect($installments->count())->toBe(5);

        $lastDue = \Carbon\Carbon::parse($installments->last()->due_date);
        $lastAllowed = \Carbon\Carbon::parse($event->start_date)->subWeek();

        expect($lastDue->lte($lastAllowed))->toBeTrue();
    });
  
});


it('throws if not enough time to create at least 2 installments', function () {
    Carbon::setTestNow('2025-10-31 10:00:00');

    $event = Event::first();
    $booking = Booking::where('payment_plan', 'PAY_IN_FULL')->where('event_id', 1)->first();
    $event->start_date = Carbon::now()->addDays(20); // start_date : 20 days from now
    $event->save();

    $booking->load('event');

    $adjustmentsRepo = Mockery::mock(AdjustmentsRepository::class);
    $adjustmentsRepo->shouldReceive('getPaidInFullId')->once()->andReturn(2);
    app()->instance(AdjustmentsRepository::class, $adjustmentsRepo);

    $bookingRepo = app(BookingRepository::class);

    $closure = fn () => $bookingRepo->changePaymentPlan($booking, 'INSTALLMENTS', 3);

    expect($closure)->toThrow(InvalidArgumentException::class);
});

it('converts INSTALLMENTS to PAY_IN_FULL, attaches adjustment and removes unpaid installments except first', function () {
    $event = Event::first();
    $booking = Booking::where('payment_plan', 'INSTALLMENTS')->where('event_id', 1)->first();
    expect($booking->payment_plan)->toBe('INSTALLMENTS');

    $adjustment = Adjustment::where('code' , 'PAID_IN_FULL')->first();
    $this->adjustmentsRepository->shouldReceive('getPaidInFullId')->andReturn($adjustment->id);
    $this->paymentInfoService->shouldReceive('syncAllocatedCost')->andReturnNull();

    $result = $this->bookingRepository->changePaymentPlan($booking, 'PAY_IN_FULL', null);
    $booking->passengers->each(function (Passenger $passenger) {
        $remaining = Installment::where('passenger_id', $passenger->id)->where('type', 'PAYMENT')->get();
        expect($remaining->count())->toBe(1);
    });

    expect($booking->adjustments()->where('adjustments.id', $adjustment->id)->exists())->toBeTrue();
    expect($result['booking']->payment_plan)->toBe('PAY_IN_FULL');
});

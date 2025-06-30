<?php

use App\Http\Requests\StoreBookingRequest;
use App\Repositories\BookingRepository;
use App\Repositories\CustomerBookingRepository;
use App\Services\CustomerBookingService;
use App\Models\{Booking,
    Cabin,
    CabinCategory,
    CabinCategorySpec,
    CabinType,
    Cart,
    Event,
    Organization,
    Passenger,
    SurvivorNumber,
    User,
    UserDetail
};
use App\Repositories\PassengerRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\Unit\utils\CabinTypeSeeder;
use Tests\TestCase;
use App\Http\Controllers\Api\Customer\BookingController;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CabinTypeSeeder::class);

    $this->bookingRepository = Mockery::mock('App\Repositories\BookingRepository');
    $this->customerBookingService = Mockery::mock('App\Services\CustomerBookingService');
    $this->customerBookingRepository = Mockery::mock('App\Repositories\CustomerBookingRepository');
    $this->bookingController = new BookingController(
        $this->bookingRepository,
        $this->customerBookingService,
        $this->customerBookingRepository,
    );
});

describe('store', function () {
    it('fails validation with empty input', function () {
        $request = app(StoreBookingRequest::class);

        $request->validateResolved();

        $controller = app(BookingController::class);
        $controller->store($request);
    })->throws(ValidationException::class);

    test('store booking request validates required rules correctly', function (string $field, mixed $invalidValue) {
        $validData = getBaseValidData();
        Arr::set($validData, $field, $invalidValue);

        $request = new StoreBookingRequest();
        $rules = $request->rules();
        $validator = Validator::make($validData, $rules);

        expect($validator->fails())->toBeTrue("Expected validation to fail for {$field} = " . var_export($invalidValue, true));
    })->with('fieldValidationMatrix');

    it('returns 400 with "Cart is empty" message when no user is authenticated', function () {
        // Make sure no user is logged in
        Auth::logout();

        // Prepare a valid StoreBookingRequest (minimal valid data)
        $data = getBaseValidData();

        $request = StoreBookingRequest::create('/booking-init', 'POST', $data);
        $request->setUserResolver(fn() => null); // no authenticated user
        $request->setContainer(app());
        $request->validateResolved();

        // Call the controller method
        $response = $this->bookingController->store($request);

        // Assert response is a JsonResponse with status 400 and correct message
        expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
        expect($response->getStatusCode())->toBe(400);
        expect($response->getData(true))->toMatchArray(['message' => 'Cart is empty']);
    });

    it('returns 400 with "Cart is empty" message when cart is empty', function () {
        $org = Organization::factory()->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();

        Auth::login($user);

        // Prepare a valid StoreBookingRequest (minimal valid data)
        $data = getBaseValidData();

        $request = StoreBookingRequest::create('/booking-init', 'POST', $data);
        $request->setUserResolver(fn() => $user); // Simulate logged-in user
        $request->setContainer(app());
        $request->validateResolved();

        // Call the controller method
        $response = $this->bookingController->store($request);

        // Assert response is a JsonResponse with status 400 and correct message
        expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
        expect($response->getStatusCode())->toBe(400);
        expect($response->getData(true))->toMatchArray(['message' => 'Cart is empty']);
    });

    it('returns 400 with "Date of birth is required" message when dob missing', function () {
        $org = Organization::factory()->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();

        Auth::login($user);

        // Prepare a valid StoreBookingRequest (minimal valid data)
        $data = getBaseValidData();

        Cart::factory([
            'user_id' => $user->id,
        ])->create();

        $request = StoreBookingRequest::create('/booking-init', 'POST', $data);
        $request->setUserResolver(fn() => $user); // Simulate logged-in user
        $request->setContainer(app());
        $request->validateResolved();

        // Call the controller method
        $response = $this->bookingController->store($request);

        // Assert response is a JsonResponse with status 400 and correct message
        expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
        expect($response->getStatusCode())->toBe(400);
        expect($response->getData(true))->toMatchArray(['message' => 'Date of birth is required']);
    });

    it('returns 201 when booking created but no confirmation email was sent', function () {
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();
        $survivorNumber = SurvivorNumber::factory()->create([
            'user_id' => $user->id,
        ]);

        UserDetail::factory([
            'user_id' => $user->id,
        ])->create();

        Auth::login($user);

        $specifiedRequestParams = [
            'cart' => [
                'event_id' => (string) $event->id,
            ],
        ];

        // Prepare a valid StoreBookingRequest (minimal valid data)
        $data = getBaseValidData($specifiedRequestParams);

        Cart::factory([
            'user_id' => $user->id,
            'cart_data' => json_decode('{"event_id":"1","cabin_type":"private-cabin","addons":[{"id":13,"code":"TAX","type":"ADDON","operation":"FIXED","value":"494.00","restrictions":null,"event_id":1,"created_at":"2025-03-28T17:15:58.000000Z","updated_at":"2025-03-28T21:46:21.000000Z","system":true},{"id":7,"code":"MEMBERSHIP_BLACK","type":"DISCOUNT","operation":"PERCENTAGE","value":"10.00","restrictions":null,"event_id":1,"created_at":"2025-03-28T17:15:58.000000Z","updated_at":"2025-03-28T17:15:58.000000Z","system":true}],"step":7,"payment_plan":"INSTALLMENTS","number_of_installments":"5","choose_your_cabin":false,"cabin_conf_accp":true,"cabin_number":null,"reservation_id":215,"reservation_timestamp":"Wed, 11 Jun 2025 22:07:13 GMT","cabin_price":"1366.00","cabin_capacity":4,"cabin_code":"3V","cabin_category":5,"cabin_category_decks":"6,7,8,9,10","cabin_category_type":"Interior","single_t_agreement":false,"time_to_cancel":20,"price_total":6893.6,"price_total_passenger":1723.4,"price_save":"136.60","price_extras":494,"tax":"494.00","lower_bed_type_2":null}', true),
        ])->create();

        $request = StoreBookingRequest::create('/booking-init', 'POST', $data);
        $request->setUserResolver(fn() => $user); // Simulate logged-in user
        $request->setContainer(app());
        $session = Session::driver(); // usually 'file' or 'array' in tests
        $request->setLaravelSession($session);
        $request->validateResolved();

        $mockBooking = [
            'booking_code' => '',
            'booking_request_id' => '',
        ];
        $mockPassenger = [
            'email' => $user->email,
            'postal_code' => '',
        ];

        $this->bookingRepository
            ->shouldReceive('createBooking')
            ->once()
            ->andReturn([
                'message' => 'Booking created successfully.',
                'booking' => $mockBooking,
                'passenger' => $mockPassenger,
            ]);

        // Call the controller method
        $response = $this->bookingController->store($request);

        // Assert response is a JsonResponse with status 400 and correct message
        expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
        expect($response->getStatusCode())->toBe(201);
        expect($response->getData(true))->toMatchArray(['message' => 'Booking created successfully, but failed to send confirmation email.']);
    });

    it('returns 201 when booking created', function () {
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory([
            'organization_id' => $org->id,
            'username' => 'MOCK_USERNAME',
        ])->create();
        $survivorNumber = SurvivorNumber::factory()->create([
            'user_id' => $user->id,
        ]);

        UserDetail::factory([
            'user_id' => $user->id,
        ])->create();

        Auth::login($user);

        $specifiedRequestParams = [
            'cart' => [
                'event_id' => (string)$event->id,
            ],
        ];

        $data = getBaseValidData($specifiedRequestParams);

        Cart::factory([
            'user_id' => $user->id,
            'cart_data' => json_decode('{"event_id":"1","cabin_type":"private-cabin","addons":[{"id":13,"code":"TAX","type":"ADDON","operation":"FIXED","value":"494.00","restrictions":null,"event_id":1,"created_at":"2025-03-28T17:15:58.000000Z","updated_at":"2025-03-28T21:46:21.000000Z","system":true},{"id":7,"code":"MEMBERSHIP_BLACK","type":"DISCOUNT","operation":"PERCENTAGE","value":"10.00","restrictions":null,"event_id":1,"created_at":"2025-03-28T17:15:58.000000Z","updated_at":"2025-03-28T17:15:58.000000Z","system":true}],"step":7,"payment_plan":"INSTALLMENTS","number_of_installments":"5","choose_your_cabin":false,"cabin_conf_accp":true,"cabin_number":null,"reservation_id":215,"reservation_timestamp":"Wed, 11 Jun 2025 22:07:13 GMT","cabin_price":"1366.00","cabin_capacity":4,"cabin_code":"3V","cabin_category":5,"cabin_category_decks":"6,7,8,9,10","cabin_category_type":"Interior","single_t_agreement":false,"time_to_cancel":20,"price_total":6893.6,"price_total_passenger":1723.4,"price_save":"136.60","price_extras":494,"tax":"494.00","lower_bed_type_2":null}', true),
        ])->create();

        $request = StoreBookingRequest::create('/booking-init', 'POST', $data);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $session = Session::driver();
        $request->setLaravelSession($session);
        $request->validateResolved();

        $mockBooking = [
            'booking_code' => 'MOCK_BOOKING_CODE',
            'booking_request_id' => '',
        ];
        $mockPassenger = [
            'email' => $user->email,
            'postal_code' => '',
        ];

        $booking = Booking::factory([
            'booking_code' => $mockBooking['booking_code'],
            'event_id' => $event->id,
            'customer_id' => $user->id,
        ])->create();

        $this->bookingRepository
            ->shouldReceive('createBooking')
            ->once()
            ->andReturn([
                'message' => 'Booking created successfully.',
                'booking' => $mockBooking,
                'passenger' => $mockPassenger,
            ]);

        $response = $this->bookingController->store($request);

        expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
        expect($response->getStatusCode())->toBe(201);
        expect($response->getData(true))->toMatchArray(['message' => 'Booking created successfully.']);
    });

    dataset('fieldValidationMatrix', function () {
        return [
            // Format: [field, invalidValue]
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['language', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['address_line_1', $invalid])
                ->all(),
            ...collect([-5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['address_line_2', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['city', $invalid])
                ->all(),
            ...collect([-5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['state', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['zip_code', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['country', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], 'string', true, false])
                ->map(fn($invalid) => ['email', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], 'string', true, false])
                ->map(fn($invalid) => ['confirm_email', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['info', $invalid])
                ->all(),
            ...collect([-5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['referral_details', $invalid])
                ->all(),
            ...collect([-5, 5, [], 'string'])
                ->map(fn($invalid) => ['travel_info', $invalid])
                ->all(),
            ...collect([-5, 5, [], 'string'])
                ->map(fn($invalid) => ['newsletter', $invalid])
                ->all(),
            ...collect([-5, 0, 5, 'string', true, false])
                ->map(fn($invalid) => ['special_options', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], 'string'])
                ->map(fn($invalid) => ['terms', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['payment_method', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['phone_number', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['emergency_contact_name', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['emergency_phone_number', $invalid])
                ->all(),

            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['bed_config', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['cart.event_id', $invalid])
                ->all(),
            ...collect([-5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['cart.cabin_type', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['cart.payment_plan', $invalid])
                ->all(),
            ...collect([-5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['cart.number_of_installments', $invalid])
                ->all(),
            ...collect([-5, 0, [], 'string', true, false])
                ->map(fn($invalid) => ['cart.reservation_id', $invalid])
                ->all(),
            ...collect([null, '', -5, [], 'string', true, false])
                ->map(fn($invalid) => ['cart.cabin_capacity', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, [], 'string', true, false])
                ->map(fn($invalid) => ['cart.cabin_category', $invalid])
                ->all(),
            ...collect([-5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['cart.cabin_code', $invalid])
                ->all(),
            ...collect([-5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['cart.cabin_price', $invalid])
                ->all(),
            ...collect([null, '', -5, 5, [], 'string',])
                ->map(fn($invalid) => ['cart.choose_your_cabin', $invalid])
                ->all(),
            ...collect([-5, [], 'string', true, false])
                ->map(fn($invalid) => ['cart.cabin_number', $invalid])
                ->all(),
            ...collect([null, '', -5, [], 'string', true, false])
                ->map(fn($invalid) => ['cart.price_total', $invalid])
                ->all(),
            ...collect([null, '', -5, 5, [], 'string',])
                ->map(fn($invalid) => ['cart.cabin_conf_accp', $invalid])
                ->all(),
            ...collect([null, '', -5, 5, [], 'string',])
                ->map(fn($invalid) => ['cart.single_t_agreement', $invalid])
                ->all(),
            ...collect([null, '', -5, 0, 5, [], true, false])
                ->map(fn($invalid) => ['cart.cabin_title', $invalid])
                ->all(),

            ...collect([-5, 0, 5, 'string', true, false])
                ->map(fn($invalid) => ['cart.addons', $invalid])
                ->all(),
            ...collect([-5, 0, 5, 'string', true, false])
                ->map(fn($invalid) => ['cart.addons.*', $invalid])
                ->all(),
        ];
    });

    function getBaseValidData($params = []): array
    {
        $base = [
            'language' => 'en',
            'address_line_1' => '123 Street',
            'address_line_2' => 'Apt 456',
            'city' => 'City',
            'state' => 'State',
            'zip_code' => '12345',
            'country' => 'Country',
            'email' => 'test@example.com',
            'confirm_email' => 'test@example.com',
            'info' => 'Internet',
            'referral_details' => null,
            'travel_info' => true,
            'newsletter' => false,
            'special_options' => [],
            'special_request' => 'None',
            'terms' => '1',
            'payment_method' => 'credit_card',
            'phone_number' => '1234567890',
            'emergency_phone_number' => '0987654321',
            'emergency_contact_name' => 'John Doe',
            'bed_config' => 'single',
            'cart' => [
                'event_id' => '1',
                'cabin_type' => 'Luxury',
                'payment_plan' => 'installments',
                'number_of_installments' => '3',
                'reservation_id' => 123,
                'cabin_capacity' => 2,
                'cabin_category' => 1,
                'cabin_code' => 'C123',
                'cabin_price' => '2500',
                'choose_your_cabin' => true,
                'cabin_number' => 101,
                'price_total' => 5000,
                'cabin_conf_accp' => true,
                'single_t_agreement' => true,
                'cabin_title' => 'Ocean View',
                'addons' => [],
            ],
        ];

        return array_replace_recursive($base, $params);
    }
});

<?php

namespace Tests\Unit;

use App\Enums\Permissions;
use App\Models\Booking;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Models\CabinCategorySpec;
use App\Models\CabinSpec;
use App\Models\CabinType;
use App\Models\Cruise;
use App\Models\Event;
use App\Models\Organization;
use App\Models\SurvivorNumber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use App\Http\Controllers\BookingsController;
use Mockery;
use Spatie\Permission\PermissionRegistrar;

class BookingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\CabinTypeSeeder::class);

        $this->eventRepository = Mockery::mock('App\Repositories\EventRepository');
        $this->bookingRepository = Mockery::mock('App\Repositories\BookingRepository');
        $this->logRepository = Mockery::mock('App\Repositories\LogRepository');
        $this->teamRepository = Mockery::mock('App\Repositories\TeamRepository');
        $this->cabinRepository = Mockery::mock('App\Repositories\CabinRepository');
        $this->cabinCategoryRepository = Mockery::mock('App\Repositories\CabinCategoryRepository');
        $this->adjustmentsRepository = Mockery::mock('App\Repositories\AdjustmentsRepository');
        $this->paymentInfoService = Mockery::mock('App\Services\PaymentInfoService');

        $this->controller = new BookingsController(
            $this->eventRepository,
            $this->bookingRepository,
            $this->logRepository,
            $this->teamRepository,
            $this->cabinRepository,
            $this->cabinCategoryRepository,
            $this->adjustmentsRepository,
            $this->paymentInfoService
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidFieldDataProvider')]
    public function test_store_fails_validation_for_invalid_input(string $field, mixed $invalidValue)
    {
        $this->expectException(ValidationException::class);

        $eventId = 123;
        $requestData = $this->getValidRequestData();

        // Support nested fields like 'passenger.email'
        data_set($requestData, $field, $invalidValue);

        $request = \Illuminate\Http\Request::create(
            "/events/{$eventId}/bookings/createManual",
            'POST',
            $requestData
        );

        $request->setRouteResolver(fn() => new class($eventId) {
            public function __construct(private $eventId)
            {
            }

            public function parameter($key)
            {
                return $key === 'id' ? $this->eventId : null;
            }
        });

        $this->controller->store($request);
    }

    public static function invalidFieldDataProvider(): array
    {
        return [
            //cabin_number
            'cabin_number = null' => ['cabin_number', null],
            'cabin_number = invalid string' => ['cabin_number', 'string'],
            'cabin_number = empty string' => ['cabin_number', ''],
            'cabin_number = negative number' => ['cabin_number', -5],
            'cabin_number = zero' => ['cabin_number', 0],
            'cabin_number = positive number' => ['cabin_number', 5],
            'cabin_number = false' => ['cabin_number', false],
            'cabin_number = true' => ['cabin_number', true],
            'cabin_number = empty array' => ['cabin_number', []],

            //payment_plan
            'payment_plan = null' => ['payment_plan', null],
            'payment_plan = invalid string' => ['payment_plan', 'string'],
            'payment_plan = empty string' => ['payment_plan', ''],
            'payment_plan = negative number' => ['payment_plan', -5],
            'payment_plan = zero' => ['payment_plan', 0],
            'payment_plan = positive number' => ['payment_plan', 5],
            'payment_plan = false' => ['payment_plan', false],
            'payment_plan = true' => ['payment_plan', true],
            'payment_plan = empty array' => ['payment_plan', []],

            //carbon_offset
            'carbon_offset = null' => ['carbon_offset', null],
            'carbon_offset = invalid string' => ['carbon_offset', 'string'],
            'carbon_offset = empty string' => ['carbon_offset', ''],
            'carbon_offset = negative number' => ['carbon_offset', -5],
            'carbon_offset = positive number' => ['carbon_offset', 5],
            'carbon_offset = empty array' => ['carbon_offset', []],

            //number_of_installments
            'number_of_installments = null' => ['number_of_installments', null],
            'number_of_installments = invalid string' => ['number_of_installments', 'string'],
            'number_of_installments = empty string' => ['number_of_installments', ''],
            'number_of_installments = negative number' => ['number_of_installments', -5],
            'number_of_installments = zero' => ['number_of_installments', 0],
            'number_of_installments = false' => ['number_of_installments', false],
            'number_of_installments = empty array' => ['number_of_installments', []],

            //passenger.id
            'passenger.id = null' => ['passenger.id', null],
            'passenger.id = empty string' => ['passenger.id', ''],
            'passenger.id = negative number' => ['passenger.id', -5],
            'passenger.id = zero' => ['passenger.id', 0],
            'passenger.id = positive number' => ['passenger.id', 5],
            'passenger.id = false' => ['passenger.id', false],
            'passenger.id = true' => ['passenger.id', true],
            'passenger.id = empty array' => ['passenger.id', []],

            //passenger.first_name
            'passenger.first_name = null' => ['passenger.first_name', null],
            'passenger.first_name = empty string' => ['passenger.first_name', ''],
            'passenger.first_name = negative number' => ['passenger.first_name', -5],
            'passenger.first_name = zero' => ['passenger.first_name', 0],
            'passenger.first_name = positive number' => ['passenger.first_name', 5],
            'passenger.first_name = false' => ['passenger.first_name', false],
            'passenger.first_name = true' => ['passenger.first_name', true],
            'passenger.first_name = empty array' => ['passenger.first_name', []],

            //passenger.last_name
            'passenger.last_name = null' => ['passenger.last_name', null],
            'passenger.last_name = empty string' => ['passenger.last_name', ''],
            'passenger.last_name = negative number' => ['passenger.last_name', -5],
            'passenger.last_name = zero' => ['passenger.last_name', 0],
            'passenger.last_name = positive number' => ['passenger.last_name', 5],
            'passenger.last_name = false' => ['passenger.last_name', false],
            'passenger.last_name = true' => ['passenger.last_name', true],
            'passenger.last_name = empty array' => ['passenger.last_name', []],

            //passenger.middle_name
            'passenger.middle_name = negative number' => ['passenger.middle_name', -5],
            'passenger.middle_name = zero' => ['passenger.middle_name', 0],
            'passenger.middle_name = positive number' => ['passenger.middle_name', 5],
            'passenger.middle_name = false' => ['passenger.middle_name', false],
            'passenger.middle_name = true' => ['passenger.middle_name', true],
            'passenger.middle_name = empty array' => ['passenger.middle_name', []],

            // passenger.dob
            'passenger.dob = null' => ['passenger.dob', null],
            'passenger.dob = invalid string' => ['passenger.dob', 'string'],
            'passenger.dob = empty string' => ['passenger.dob', ''],
            'passenger.dob = negative number' => ['passenger.dob', -5],
            'passenger.dob = zero' => ['passenger.dob', 0],
            'passenger.dob = positive number' => ['passenger.dob', 5],
            'passenger.dob = false' => ['passenger.dob', false],
            'passenger.dob = true' => ['passenger.dob', true],
            'passenger.dob = empty array' => ['passenger.dob', []],

            // passenger.gender
            'passenger.gender = null' => ['passenger.gender', null],
            'passenger.gender = invalid string' => ['passenger.gender', 'string'],
            'passenger.gender = empty string' => ['passenger.gender', ''],
            'passenger.gender = negative number' => ['passenger.gender', -5],
            'passenger.gender = zero' => ['passenger.gender', 0],
            'passenger.gender = positive number' => ['passenger.gender', 5],
            'passenger.gender = false' => ['passenger.gender', false],
            'passenger.gender = true' => ['passenger.gender', true],
            'passenger.gender = empty array' => ['passenger.gender', []],

            // passenger.citizenship
            'passenger.citizenship = invalid string' => ['passenger.citizenship', 'string'],
            'passenger.citizenship = negative number' => ['passenger.citizenship', -5],
            'passenger.citizenship = zero' => ['passenger.citizenship', 0],
            'passenger.citizenship = positive number' => ['passenger.citizenship', 5],
            'passenger.citizenship = false' => ['passenger.citizenship', false],
            'passenger.citizenship = true' => ['passenger.citizenship', true],
            'passenger.citizenship = empty array' => ['passenger.citizenship', []],

            // passenger.email
            'passenger.email = null' => ['passenger.email', null],
            'passenger.email = invalid string' => ['passenger.email', 'string'],
            'passenger.email = empty string' => ['passenger.email', ''],
            'passenger.email = negative number' => ['passenger.email', -5],
            'passenger.email = zero' => ['passenger.email', 0],
            'passenger.email = positive number' => ['passenger.email', 5],
            'passenger.email = false' => ['passenger.email', false],
            'passenger.email = true' => ['passenger.email', true],
            'passenger.email = empty array' => ['passenger.email', []],

            // passenger.phone
            'passenger.phone = negative number' => ['passenger.phone', -5],
            'passenger.phone = zero' => ['passenger.phone', 0],
            'passenger.phone = positive number' => ['passenger.phone', 5],
            'passenger.phone = false' => ['passenger.phone', false],
            'passenger.phone = true' => ['passenger.phone', true],
            'passenger.phone = empty array' => ['passenger.phone', []],

            // passenger.address_first
            'passenger.address_first = null' => ['passenger.address_first', null],
            'passenger.address_first = empty string' => ['passenger.address_first', ''],
            'passenger.address_first = negative number' => ['passenger.address_first', -5],
            'passenger.address_first = zero' => ['passenger.address_first', 0],
            'passenger.address_first = positive number' => ['passenger.address_first', 5],
            'passenger.address_first = false' => ['passenger.address_first', false],
            'passenger.address_first = true' => ['passenger.address_first', true],
            'passenger.address_first = empty array' => ['passenger.address_first', []],

            // passenger.address_second
            'passenger.address_second = negative number' => ['passenger.address_second', -5],
            'passenger.address_second = zero' => ['passenger.address_second', 0],
            'passenger.address_second = positive number' => ['passenger.address_second', 5],
            'passenger.address_second = false' => ['passenger.address_second', false],
            'passenger.address_second = true' => ['passenger.address_second', true],
            'passenger.address_second = empty array' => ['passenger.address_second', []],

            // passenger.city
            'passenger.city = null' => ['passenger.city', null],
            'passenger.city = empty string' => ['passenger.city', ''],
            'passenger.city = negative number' => ['passenger.city', -5],
            'passenger.city = zero' => ['passenger.city', 0],
            'passenger.city = positive number' => ['passenger.city', 5],
            'passenger.city = false' => ['passenger.city', false],
            'passenger.city = true' => ['passenger.city', true],
            'passenger.city = empty array' => ['passenger.city', []],

            // passenger.state
            'passenger.state = negative number' => ['passenger.state', -5],
            'passenger.state = zero' => ['passenger.state', 0],
            'passenger.state = positive number' => ['passenger.state', 5],
            'passenger.state = false' => ['passenger.state', false],
            'passenger.state = true' => ['passenger.state', true],
            'passenger.state = empty array' => ['passenger.state', []],

            // passenger.postal_code
            'passenger.postal_code = negative number' => ['passenger.postal_code', -5],
            'passenger.postal_code = zero' => ['passenger.postal_code', 0],
            'passenger.postal_code = positive number' => ['passenger.postal_code', 5],
            'passenger.postal_code = false' => ['passenger.postal_code', false],
            'passenger.postal_code = true' => ['passenger.postal_code', true],
            'passenger.postal_code = empty array' => ['passenger.postal_code', []],

            // passenger.country
            'passenger.country = null' => ['passenger.country', null],
            'passenger.country = invalid string' => ['passenger.country', 'string'],
            'passenger.country = negative number' => ['passenger.country', -5],
            'passenger.country = zero' => ['passenger.country', 0],
            'passenger.country = positive number' => ['passenger.country', 5],
            'passenger.country = false' => ['passenger.country', false],
            'passenger.country = true' => ['passenger.country', true],
            'passenger.country = empty array' => ['passenger.country', []],

            // passenger.emergency_c_name
            'passenger.emergency_c_name = negative number' => ['passenger.emergency_c_name', -5],
            'passenger.emergency_c_name = zero' => ['passenger.emergency_c_name', 0],
            'passenger.emergency_c_name = positive number' => ['passenger.emergency_c_name', 5],
            'passenger.emergency_c_name = false' => ['passenger.emergency_c_name', false],
            'passenger.emergency_c_name = true' => ['passenger.emergency_c_name', true],
            'passenger.emergency_c_name = empty array' => ['passenger.emergency_c_name', []],

            // passenger.emergency_c_phone
            'passenger.emergency_c_phone = negative number' => ['passenger.emergency_c_phone', -5],
            'passenger.emergency_c_phone = zero' => ['passenger.emergency_c_phone', 0],
            'passenger.emergency_c_phone = positive number' => ['passenger.emergency_c_phone', 5],
            'passenger.emergency_c_phone = false' => ['passenger.emergency_c_phone', false],
            'passenger.emergency_c_phone = true' => ['passenger.emergency_c_phone', true],
            'passenger.emergency_c_phone = empty array' => ['passenger.emergency_c_phone', []],

            // passenger.payment_method
            'passenger.payment_method = null' => ['passenger.payment_method', null],
            'passenger.payment_method = invalid string' => ['passenger.payment_method', 'BITCOIN'],
            'passenger.payment_method = empty string' => ['passenger.payment_method', ''],
            'passenger.payment_method = negative number' => ['passenger.payment_method', -5],
            'passenger.payment_method = zero' => ['passenger.payment_method', 0],
            'passenger.payment_method = positive number' => ['passenger.payment_method', 5],
            'passenger.payment_method = false' => ['passenger.payment_method', false],
            'passenger.payment_method = true' => ['passenger.payment_method', true],
            'passenger.payment_method = empty array' => ['passenger.payment_method', []],

            // passenger.special_request
            'passenger.special_request = negative number' => ['passenger.special_request', -5],
            'passenger.special_request = zero' => ['passenger.special_request', 0],
            'passenger.special_request = positive number' => ['passenger.special_request', 5],
            'passenger.special_request = false' => ['passenger.special_request', false],
            'passenger.special_request = true' => ['passenger.special_request', true],
            'passenger.special_request = empty array' => ['passenger.special_request', []],

            // passenger.lead_passenger
            'passenger.lead_passenger = null' => ['passenger.lead_passenger', null],
            'passenger.lead_passenger = invalid string' => ['passenger.lead_passenger', 'string'],
            'passenger.lead_passenger = empty string' => ['passenger.lead_passenger', ''],
            'passenger.lead_passenger = negative number' => ['passenger.lead_passenger', -5],
            'passenger.lead_passenger = positive number' => ['passenger.lead_passenger', 5],
            'passenger.lead_passenger = empty array' => ['passenger.lead_passenger', []],

            // passenger.travel_info
            'passenger.travel_info = null' => ['passenger.travel_info', null],
            'passenger.travel_info = invalid string' => ['passenger.travel_info', 'string'],
            'passenger.travel_info = empty string' => ['passenger.travel_info', ''],
            'passenger.travel_info = negative number' => ['passenger.travel_info', -5],
            'passenger.travel_info = positive number' => ['passenger.travel_info', 5],
            'passenger.travel_info = empty array' => ['passenger.travel_info', []],

            // passenger.terms_n_cons
            'passenger.terms_n_cons = null' => ['passenger.terms_n_cons', null],
            'passenger.terms_n_cons = invalid string' => ['passenger.terms_n_cons', 'string'],
            'passenger.terms_n_cons = empty string' => ['passenger.terms_n_cons', ''],
            'passenger.terms_n_cons = negative number' => ['passenger.terms_n_cons', -5],
            'passenger.terms_n_cons = zero' => ['passenger.terms_n_cons', 0],
            'passenger.terms_n_cons = positive number' => ['passenger.terms_n_cons', 5],
            'passenger.terms_n_cons = false' => ['passenger.terms_n_cons', false],
            'passenger.terms_n_cons = empty array' => ['passenger.terms_n_cons', []],

            // passenger.single_t_agreement
            'passenger.single_t_agreement = null' => ['passenger.single_t_agreement', null],
            'passenger.single_t_agreement = invalid string' => ['passenger.single_t_agreement', 'string'],
            'passenger.single_t_agreement = empty string' => ['passenger.single_t_agreement', ''],
            'passenger.single_t_agreement = negative number' => ['passenger.single_t_agreement', -5],
            'passenger.single_t_agreement = positive number' => ['passenger.single_t_agreement', 5],
            'passenger.single_t_agreement = empty array' => ['passenger.single_t_agreement', []],

            // passenger.newsletter
            'passenger.newsletter = invalid string' => ['passenger.newsletter', 'string'],
            'passenger.newsletter = negative number' => ['passenger.newsletter', -5],
            'passenger.newsletter = positive number' => ['passenger.newsletter', 5],
            'passenger.newsletter = empty array' => ['passenger.newsletter', []],

            // passenger.passenger_allocated_cost
            'passenger.passenger_allocated_cost = invalid string' => ['passenger.passenger_allocated_cost', 'string'],
            'passenger.passenger_allocated_cost = negative number' => ['passenger.passenger_allocated_cost', -5],
            'passenger.passenger_allocated_cost = false' => ['passenger.passenger_allocated_cost', false],
            'passenger.passenger_allocated_cost = true' => ['passenger.passenger_allocated_cost', true],
            'passenger.passenger_allocated_cost = empty array' => ['passenger.passenger_allocated_cost', []],

            // passenger.passenger_balance
            'passenger.passenger_balance = invalid string' => ['passenger.passenger_balance', 'string'],
            'passenger.passenger_balance = negative number' => ['passenger.passenger_balance', -5],
            'passenger.passenger_balance = false' => ['passenger.passenger_balance', false],
            'passenger.passenger_balance = true' => ['passenger.passenger_balance', true],
            'passenger.passenger_balance = empty array' => ['passenger.passenger_balance', []],

            // passenger.was_on_board currently not validated
            //'passenger.was_on_board = null' => ['passenger.was_on_board', null],
            //'passenger.was_on_board = invalid string' => ['passenger.was_on_board', 'string'],
            //'passenger.was_on_board = empty string' => ['passenger.was_on_board', ''],
            //'passenger.was_on_board = negative number' => ['passenger.was_on_board', -5],
            //'passenger.was_on_board = zero' => ['passenger.was_on_board', 0],
            //'passenger.was_on_board = positive number' => ['passenger.was_on_board', 5],
            //'passenger.was_on_board = false' => ['passenger.was_on_board', false],
            //'passenger.was_on_board = true' => ['passenger.was_on_board', true],
            //'passenger.was_on_board = empty array' => ['passenger.was_on_board', []],

            // passenger.language currently not validated
            //'passenger.language = null' => ['passenger.language', null],
            //'passenger.language = invalid string' => ['passenger.language', 'string'],
            //'passenger.language = empty string' => ['passenger.language', ''],
            //'passenger.language = negative number' => ['passenger.language', -5],
            //'passenger.language = zero' => ['passenger.language', 0],
            //'passenger.language = positive number' => ['passenger.language', 5],
            //'passenger.language = false' => ['passenger.language', false],
            //'passenger.language = true' => ['passenger.language', true],
            //'passenger.language = empty array' => ['passenger.language', []],
        ];
    }

    private function getValidRequestData()
    {
        $cabinSpec = CabinSpec::query()->first();
        if (!$cabinSpec) {
            $cabinSpec = CabinSpec::factory()->create();
        }
        $org = Organization::factory()->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();

        return [
            'cabin_number' => $cabinSpec->cabin_number,
            'payment_plan' => 'INSTALLMENTS',
            'carbon_offset' => false,
            'number_of_installments' => 4,
            'passenger' => [
                'id' => $user->id,
                'first_name' => 'John',
                'middle_name' => null,
                'last_name' => 'Doe',
                'dob' => '1990-01-01',
                'gender' => 'M',
                'citizenship' => 'USA',
                'survivor_number' => '123456789',
                'email' => 'john@example.com',
                'phone' => '+36301234567',
                'address_first' => '123 Main St',
                'address_second' => null,
                'city' => 'Anytown',
                'state' => 'Minnesota',
                'postal_code' => '1234',
                'country' => 'USA',
                'emergency_c_name' => 'Emergency Name',
                'emergency_c_phone' => '+36201234567',
                'payment_method' => 'CREDIT_CARD',
                'special_request' => null,
                'lead_passenger' => true,
                'travel_info' => true,
                'terms_n_cons' => '1',
                'single_t_agreement' => true,
                'newsletter' => true,
                'passenger_allocated_cost' => null,
                'passenger_balance' => null,
                'was_on_board' => false,
                'language' => 'en',
            ],
        ];
    }

    public function test_store_succeeds_on_valid_input_with_installments()
    {
        $cruise = Cruise::factory()->create();
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();
        $survivorNumber = SurvivorNumber::factory()->create([
            'user_id' => $user->id,
        ]);

        $cabinType = CabinType::query()->where('cabin_type', 'Private Cabin')->first();
        $cabinTypeId = $cabinType->id;

        do {
            $cabinNumber = (string)random_int(1000, 99999); // Adjust range if needed
        } while (CabinSpec::where('cabin_number', $cabinNumber)->exists());

        $cabinSpec = CabinSpec::factory([
            'cabin_number' => $cabinNumber,
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

        $cabinCategorySpec = CabinCategorySpec::factory([
            'category_type' => 'Suite',
            'category_code' => 'GT',
            'category_name' => 'Grand Suite - 2 Bedroom',
            'capacity' => 4,
            'description' => '{"MOCK"}',
            'iframe' => 'MOCK_IFRAME',
            'images' => ["MOCK.gif"],
            'decks' => '8, 9',
            'display_order' => 69,
            'cruise_id' => $cruise->id,
            'category_number' => 4,
        ])->create();

        $cabinCategory = CabinCategory::factory([
            'price' => 3333,
            'cabin_category_spec_id' => $cabinCategorySpec->id,
            'event_id' => $event->id,
        ])->create();

        Cabin::factory([
            'cabin_type_id' => $cabinTypeId,
            'cabin_category_id' => $cabinCategory->id,
            'cabin_spec_id' => $cabinSpec->id,
            'inventory' => 0,
            'notes' => 'MOCK_NOTES',
            'tags' => '[""]',
            'status' => 'AVAILABLE',
        ])->create();

        app(PermissionRegistrar::class)->setPermissionsTeamId(1);
        Permission::findOrCreate(Permissions::CreateBookings->value, 'web'); // Ensure it exists
        $user->givePermissionTo(Permissions::CreateBookings);
        $this->actingAs($user);

        $requestData = $this->getValidRequestData();
        $requestData['passenger']['id'] = $user->id;
        $requestData['passenger']['survivor_number'] = (string)$survivorNumber->survivor_number;

        // Create a real request instance
        $this->post("/events/{$event->id}/bookings/createManual", $requestData);

        $cabinSpec = CabinSpec::where('cabin_number', $requestData['cabin_number'])
            ->firstOrFail();

        // Assert DB side-effect
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $user->id,
            'event_id' => $event->id,
            'payment_plan' => $requestData['payment_plan'],
            'cabin_id' => $cabinSpec->id,
            'is_single_occupancy' => false,
            'bed_config' => 'SEPARATED',
            'tags' => json_encode(['NEW']),
            'status' => 'NEW',
        ]);

        $booking = Booking::where('customer_id', $user->id)
            ->where('event_id', $event->id)
            ->firstOrFail();

        $this->assertDatabaseHas('passengers', [
            'booking_id' => $booking->id,
            'lead_passenger' => true,
            'survivor_number' => (string)$survivorNumber->survivor_number,
            'payment_method' => $requestData['passenger']['payment_method'],
            'gender' => $requestData['passenger']['gender'],
            'first_name' => strtoupper($requestData['passenger']['first_name']),
            'last_name' => strtoupper($requestData['passenger']['last_name']),
            'dob' => $requestData['passenger']['dob'],
            'citizenship' => $requestData['passenger']['citizenship'],
            'address_first' => $requestData['passenger']['address_first'],
            'address_second' => $requestData['passenger']['address_second'],
            'city' => $requestData['passenger']['city'],
            'state' => $requestData['passenger']['state'],
            'postal_code' => $requestData['passenger']['postal_code'],
            'country' => $requestData['passenger']['country'],
            'email' => $requestData['passenger']['email'],
            'phone' => $requestData['passenger']['phone'],
            'emergency_c_name' => $requestData['passenger']['emergency_c_name'],
            'emergency_c_phone' => $requestData['passenger']['emergency_c_phone'],
            'special_request' => $requestData['passenger']['special_request'],
            'newsletter' => $requestData['passenger']['newsletter'],
            'travel_info' => $requestData['passenger']['travel_info'],
            'empty_seat' => false,
            'single_t_agreement' => $requestData['passenger']['single_t_agreement'],
            'was_on_board' => $requestData['passenger']['was_on_board'],
            'language' => $requestData['passenger']['language'],
        ]);
    }

    public function test_store_succeeds_on_valid_input_with_paid_in_full()
    {
        $cruise = Cruise::factory()->create();
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();
        $survivorNumber = SurvivorNumber::factory()->create([
            'user_id' => $user->id,
        ]);

        $cabinType = CabinType::query()->where('cabin_type', 'Private Cabin')->first();
        $cabinTypeId = $cabinType->id;

        do {
            $cabinNumber = (string)random_int(1000, 99999); // Adjust range if needed
        } while (CabinSpec::where('cabin_number', $cabinNumber)->exists());

        $cabinSpec = CabinSpec::factory([
            'cabin_number' => $cabinNumber,
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

        $cabinCategorySpec = CabinCategorySpec::factory([
            'category_type' => 'Suite',
            'category_code' => 'GT',
            'category_name' => 'Grand Suite - 2 Bedroom',
            'capacity' => 4,
            'description' => '{"MOCK"}',
            'iframe' => 'MOCK_IFRAME',
            'images' => ["MOCK.gif"],
            'decks' => '8, 9',
            'display_order' => 69,
            'cruise_id' => $cruise->id,
            'category_number' => 4,
        ])->create();

        $cabinCategory = CabinCategory::factory([
            'price' => 3333,
            'cabin_category_spec_id' => $cabinCategorySpec->id,
            'event_id' => $event->id,
        ])->create();

        Cabin::factory([
            'cabin_type_id' => $cabinTypeId,
            'cabin_category_id' => $cabinCategory->id,
            'cabin_spec_id' => $cabinSpec->id,
            'inventory' => 0,
            'notes' => 'MOCK_NOTES',
            'tags' => '[""]',
            'status' => 'AVAILABLE',
        ])->create();

        app(PermissionRegistrar::class)->setPermissionsTeamId(1);
        Permission::findOrCreate(Permissions::CreateBookings->value, 'web'); // Ensure it exists
        $user->givePermissionTo(Permissions::CreateBookings);
        $this->actingAs($user);

        $requestData = $this->getValidRequestData();
        $requestData['payment_plan'] = 'PAY_IN_FULL';
        $requestData['number_of_installments'] = 1;
        $requestData['passenger']['id'] = $user->id;
        $requestData['passenger']['survivor_number'] = (string)$survivorNumber->survivor_number;
        $requestData['cabin_number'] = $cabinNumber;
        //$requestData['passenger']['single_t_agreement'] = true;

        // Create a real request instance
        $this->post("/events/{$event->id}/bookings/createManual", $requestData);

        // Assert DB side-effect
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $user->id,
            'event_id' => $event->id,
            'payment_plan' => 'PAY_IN_FULL',
            'cabin_id' => $cabinSpec->id,
            'is_single_occupancy' => false,
            'bed_config' => 'SEPARATED',
            'tags' => json_encode(['NEW']),
            'status' => 'NEW',
        ]);

        $booking = Booking::where('customer_id', $user->id)
            ->where('event_id', $event->id)
            ->firstOrFail();

        $this->assertDatabaseHas('passengers', [
            'booking_id' => $booking->id,
            'lead_passenger' => true,
            'survivor_number' => (string)$survivorNumber->survivor_number,
            'payment_method' => $requestData['passenger']['payment_method'],
            'gender' => $requestData['passenger']['gender'],
            'first_name' => strtoupper($requestData['passenger']['first_name']),
            'last_name' => strtoupper($requestData['passenger']['last_name']),
            'dob' => $requestData['passenger']['dob'],
            'citizenship' => $requestData['passenger']['citizenship'],
            'address_first' => $requestData['passenger']['address_first'],
            'address_second' => $requestData['passenger']['address_second'],
            'city' => $requestData['passenger']['city'],
            'state' => $requestData['passenger']['state'],
            'postal_code' => $requestData['passenger']['postal_code'],
            'country' => $requestData['passenger']['country'],
            'email' => $requestData['passenger']['email'],
            'phone' => $requestData['passenger']['phone'],
            'emergency_c_name' => $requestData['passenger']['emergency_c_name'],
            'emergency_c_phone' => $requestData['passenger']['emergency_c_phone'],
            'special_request' => $requestData['passenger']['special_request'],
            'newsletter' => $requestData['passenger']['newsletter'],
            'travel_info' => $requestData['passenger']['travel_info'],
            'empty_seat' => false,
            'single_t_agreement' => $requestData['passenger']['single_t_agreement'],
            'was_on_board' => $requestData['passenger']['was_on_board'],
            'language' => $requestData['passenger']['language'],
        ]);
    }

    public function test_store_books_the_correct_capacity_cabin_when_shared_exists()
    {
        $cruise = Cruise::factory()->create();
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory([
            'organization_id' => $org->id,
        ])->create();
        $survivorNumber = SurvivorNumber::factory()->create([
            'user_id' => $user->id,
        ]);

        $cabinType = CabinType::query()->where('cabin_type', 'Private Cabin')->first();
        $cabinTypeId = $cabinType->id;

        do {
            $cabinNumber = (string)random_int(1000, 99999); // Adjust range if needed
        } while (CabinSpec::where('cabin_number', $cabinNumber)->exists());

        app(PermissionRegistrar::class)->setPermissionsTeamId(1);
        Permission::findOrCreate(Permissions::CreateBookings->value, 'web'); // Ensure it exists
        $user->givePermissionTo(Permissions::CreateBookings);
        $this->actingAs($user);

        $cabinSpec = CabinSpec::factory([
            'cabin_number' => $cabinNumber,
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

        $cabinCategorySpec4 = CabinCategorySpec::factory([
            'category_type' => 'Suite',
            'category_code' => 'GT',
            'category_name' => 'Grand Suite - 2 Bedroom',
            'capacity' => 4,
            'description' => '{"MOCK"}',
            'iframe' => 'MOCK_IFRAME',
            'images' => ["MOCK.gif"],
            'decks' => '8, 9',
            'display_order' => 69,
            'cruise_id' => $cruise->id,
            'category_number' => 4,
        ])->create();
        $cabinCategorySpec5 = CabinCategorySpec::factory([
            'category_type' => 'Suite',
            'category_code' => 'GT',
            'category_name' => 'Grand Suite - 2 Bedroom',
            'capacity' => 5,
            'description' => '{"MOCK"}',
            'iframe' => 'MOCK_IFRAME',
            'images' => ["MOCK.gif"],
            'decks' => '8, 9',
            'display_order' => 70,
            'cruise_id' => $cruise->id,
            'category_number' => 4,
        ])->create();
        $cabinCategorySpec6 = CabinCategorySpec::factory([
            'category_type' => 'Suite',
            'category_code' => 'GT',
            'category_name' => 'Grand Suite - 2 Bedroom',
            'capacity' => 6,
            'description' => '{"MOCK"}',
            'iframe' => 'MOCK_IFRAME',
            'images' => ["MOCK.gif"],
            'decks' => '8, 9',
            'display_order' => 71,
            'cruise_id' => $cruise->id,
            'category_number' => 4,
        ])->create();
        $cabinCategorySpec7 = CabinCategorySpec::factory([
            'category_type' => 'Suite',
            'category_code' => 'GT',
            'category_name' => 'Grand Suite - 2 Bedroom',
            'capacity' => 7,
            'description' => '{"MOCK"}',
            'iframe' => 'MOCK_IFRAME',
            'images' => ["MOCK.gif"],
            'decks' => '8, 9',
            'display_order' => 72,
            'cruise_id' => $cruise->id,
            'category_number' => 4,
        ])->create();
        $cabinCategorySpec8 = CabinCategorySpec::factory([
            'category_type' => 'Suite',
            'category_code' => 'GT',
            'category_name' => 'Grand Suite - 2 Bedroom',
            'capacity' => 8,
            'description' => '{"MOCK"}',
            'iframe' => 'MOCK_IFRAME',
            'images' => ["MOCK.gif"],
            'decks' => '8, 9',
            'display_order' => 73,
            'cruise_id' => $cruise->id,
            'category_number' => 4,
        ])->create();

        $cabinCategory4 = CabinCategory::factory([
            'price' => 3333,
            'cabin_category_spec_id' => $cabinCategorySpec4->id,
            'event_id' => $event->id,
        ])->create();
        $cabinCategory5 = CabinCategory::factory([
            'price' => 3066,
            'cabin_category_spec_id' => $cabinCategorySpec5->id,
            'event_id' => $event->id,
        ])->create();
        $cabinCategory6 = CabinCategory::factory([
            'price' => 2799,
            'cabin_category_spec_id' => $cabinCategorySpec6->id,
            'event_id' => $event->id,
        ])->create();
        $cabinCategory7 = CabinCategory::factory([
            'price' => 2533,
            'cabin_category_spec_id' => $cabinCategorySpec7->id,
            'event_id' => $event->id,
        ])->create();
        $cabinCategory8 = CabinCategory::factory([
            'price' => 2266,
            'cabin_category_spec_id' => $cabinCategorySpec8->id,
            'event_id' => $event->id,
        ])->create();

        $cabin4 = Cabin::factory([
            'cabin_type_id' => $cabinTypeId,
            'cabin_category_id' => $cabinCategory4->id,
            'cabin_spec_id' => $cabinSpec->id,
            'inventory' => 0,
            'notes' => 'MOCK_NOTES',
            'tags' => '[""]',
            'status' => 'AVAILABLE',
        ])->create();
        $cabin5 = Cabin::factory([
            'cabin_type_id' => $cabinTypeId,
            'cabin_category_id' => $cabinCategory5->id,
            'cabin_spec_id' => $cabinSpec->id,
            'inventory' => 0,
            'notes' => 'MOCK_NOTES',
            'tags' => '[""]',
            'status' => 'AVAILABLE',
        ])->create();
        $cabin6 = Cabin::factory([
            'cabin_type_id' => $cabinTypeId,
            'cabin_category_id' => $cabinCategory6->id,
            'cabin_spec_id' => $cabinSpec->id,
            'inventory' => 0,
            'notes' => 'MOCK_NOTES',
            'tags' => '[""]',
            'status' => 'AVAILABLE',
        ])->create();
        $cabin7 = Cabin::factory([
            'cabin_type_id' => $cabinTypeId,
            'cabin_category_id' => $cabinCategory7->id,
            'cabin_spec_id' => $cabinSpec->id,
            'inventory' => 0,
            'notes' => 'MOCK_NOTES',
            'tags' => '[""]',
            'status' => 'AVAILABLE',
        ])->create();
        $cabin8 = Cabin::factory([
            'cabin_type_id' => $cabinTypeId,
            'cabin_category_id' => $cabinCategory8->id,
            'cabin_spec_id' => $cabinSpec->id,
            'inventory' => 0,
            'notes' => 'MOCK_NOTES',
            'tags' => '[""]',
            'status' => 'AVAILABLE',
        ])->create();

        $requestData = $this->getValidRequestData();
        $requestData['payment_plan'] = 'PAY_IN_FULL';
        $requestData['number_of_installments'] = 1;
        $requestData['passenger']['id'] = $user->id;
        $requestData['passenger']['survivor_number'] = (string)$survivorNumber->survivor_number;
        $requestData['cabin_number'] = $cabinNumber;

        // Create a real request instance
        $this->post("/events/{$event->id}/bookings/createManual", $requestData);

        $cabinSpec = CabinSpec::where('cabin_number', $requestData['cabin_number'])
            ->firstOrFail();

        // Assert DB side-effect
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $user->id,
            'event_id' => $event->id,
            'payment_plan' => 'PAY_IN_FULL',
            'cabin_id' => $cabin8->id,
            'is_single_occupancy' => false,
            'bed_config' => 'SEPARATED',
            'tags' => json_encode(['NEW']),
            'status' => 'NEW',
        ]);

        $booking = Booking::where('customer_id', $user->id)
            ->where('event_id', $event->id)
            ->firstOrFail();

        $this->assertDatabaseHas('passengers', [
            'booking_id' => $booking->id,
            'lead_passenger' => true,
            'survivor_number' => (string)$survivorNumber->survivor_number,
            'payment_method' => $requestData['passenger']['payment_method'],
            'gender' => $requestData['passenger']['gender'],
            'first_name' => strtoupper($requestData['passenger']['first_name']),
            'last_name' => strtoupper($requestData['passenger']['last_name']),
            'dob' => $requestData['passenger']['dob'],
            'citizenship' => $requestData['passenger']['citizenship'],
            'address_first' => $requestData['passenger']['address_first'],
            'address_second' => $requestData['passenger']['address_second'],
            'city' => $requestData['passenger']['city'],
            'state' => $requestData['passenger']['state'],
            'postal_code' => $requestData['passenger']['postal_code'],
            'country' => $requestData['passenger']['country'],
            'email' => $requestData['passenger']['email'],
            'phone' => $requestData['passenger']['phone'],
            'emergency_c_name' => $requestData['passenger']['emergency_c_name'],
            'emergency_c_phone' => $requestData['passenger']['emergency_c_phone'],
            'special_request' => $requestData['passenger']['special_request'],
            'newsletter' => $requestData['passenger']['newsletter'],
            'travel_info' => $requestData['passenger']['travel_info'],
            'empty_seat' => false,
            'single_t_agreement' => $requestData['passenger']['single_t_agreement'],
            'was_on_board' => $requestData['passenger']['was_on_board'],
            'language' => $requestData['passenger']['language'],
        ]);
    }
}

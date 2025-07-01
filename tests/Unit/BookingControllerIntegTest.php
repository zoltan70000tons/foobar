<?php

use App\Http\Requests\StoreBookingRequest;
use App\Repositories\BookingRepository;
use App\Repositories\CustomerBookingRepository;
use App\Services\CustomerBookingService;
use App\Models\{Booking,
    Cabin,
    CabinCategory,
    CabinCategorySpec,
    CabinSpec,
    CabinType,
    Cart,
    Cruise,
    Event,
    Membership,
    MembershipType,
    Organization,
    Passenger,
    SurvivorNumber,
    TemporaryReservation,
    User,
    UserDetail};
use App\Repositories\PassengerRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\Unit\utils\CabinTypeSeeder;
use Tests\TestCase;
use App\Http\Controllers\Api\Customer\BookingController;
use Illuminate\Support\Facades\DB;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CabinTypeSeeder::class);
    $this->seed(\Tests\Unit\utils\RoleSeeder::class);

    $this->bookingRepository = app(BookingRepository::class);
    $this->customerBookingService = Mockery::mock('App\Services\CustomerBookingService');
    $this->customerBookingRepository = Mockery::mock('App\Repositories\CustomerBookingRepository');
    $this->bookingController = new BookingController(
        $this->bookingRepository,
        $this->customerBookingService,
        $this->customerBookingRepository,
    );
});

describe('store', function () {
    it('stores booking in database', function () {
        $cruise = Cruise::factory()->create();
        $org = Organization::factory()->create();
        $event = Event::factory(['organization_id' => $org->id])->create();
        $user = User::factory([
            'organization_id' => $org->id,
            'username' => 'MOCK_USERNAME',
        ])->create();
        $survivorNumber = SurvivorNumber::factory()->create([
            'user_id' => $user->id,
        ]);

        $membershipType = MembershipType::factory()->create();
        Membership::factory([
            'user_id' => $user->id,
            'membership_id' => $membershipType->id,
        ])->create();
        DB::table('model_has_roles')->updateOrInsert(
            [
                'role_id' => 7,
                'model_type' => 'App\Models\User',
                'model_id' => $user->id,
                'team_id' => 1,
            ],
            []
        );


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

        $cabinType = CabinType::query()->where('cabin_type', 'Private Cabin')->firstOrFail();
        $cabinTypeId = $cabinType->id;

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

        $cabin = Cabin::factory([
            'cabin_type_id' => $cabinTypeId,
            'cabin_category_id' => $cabinCategory->id,
            'cabin_spec_id' => $cabinSpec->id,
            'inventory' => 0,
            'notes' => 'MOCK_NOTES',
            'tags' => '[""]',
            'status' => 'AVAILABLE',
        ])->create();

        $tempReservation = TemporaryReservation::factory([
            'cabin_id' => $cabin->id,
            'user_id' => $user->id,
            'cabin_number' => $cabinNumber,
        ])->create();

        UserDetail::factory([
            'user_id' => $user->id,
        ])->create();

        Auth::login($user);

        Cart::factory()->create([
            'user_id' => $user->id,
            'cart_data' => [
                'event_id' => 1,
                'reservation_id' => 123,
                'payment_plan' => 'FULL',
                'price_total_passenger' => 1500,
                'price_total' => 1500,
                'cabin_type' => 'private-cabin',
                'cabin_conf_accp' => true,
                'single_t_agreement' => true,
                'cabin_title' => 'Ocean View',
                'addons' => [],
                'number_of_installments' => null,
                'choose_your_cabin' => false,
                'cabin_capacity' => 2,
                'cabin_category' => 3,
            ],
        ]);

        $overrides = [
            'cart' => [
                'reservation_id' => $tempReservation->id,
                'event_id' => (string) $event->id,
            ],
            'event_id' => (string) $event->id,
        ];

        $validData = getBaseValidData($overrides);

        $response = $this->withSession(['reserved_cabin_id' => $tempReservation->id])
            ->postJson('/api/booking-init', $validData);

        expect($response->getStatusCode())->toBe(201);
        expect($response->getData(true))->toMatchArray(['message' => 'Booking created successfully.']);

        $lastBooking = Booking::latest('created_at')->first();
        expect($lastBooking->event_id)->toBe($event->id);
        expect($lastBooking->customer_id)->toBe($user->id);
        expect($lastBooking->cabin_id)->toBe($cabin->id);
        expect($lastBooking->status)->toBe('NEW');
        expect($lastBooking->payment_plan)->toBe($validData['cart']['payment_plan']);
        expect($lastBooking->bed_config)->toBe($validData['bed_config']);

        $lastPassenger = Passenger::latest('created_at')->first();
        expect($lastPassenger->booking_id)->toBe($lastBooking->id);
        expect($lastPassenger->survivor_number)->toBe((string)$survivorNumber->survivor_number);
        expect($lastPassenger->lead_passenger)->toBeTrue();
        expect($lastPassenger->payment_method)->toBe($validData['payment_method']);
        expect($lastPassenger->address_first)->toBe($validData['address_line_1']);
        expect($lastPassenger->email)->toBe($validData['email']);
        expect($lastPassenger->city)->toBe($validData['city']);
    });

    function getBaseValidData($params = []): array
    {
        $base = [
            'language' => 'en',
            'event_id' => '1',
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
            'payment_method' => 'CREDIT_CARD',
            'phone_number' => '1234567890',
            'emergency_phone_number' => '0987654321',
            'emergency_contact_name' => 'John Doe',
            'bed_config' => 'SEPARATED',
            'cart' => [
                'event_id' => '1',
                'cabin_type' => 'Luxury',
                'payment_plan' => 'INSTALLMENTS',
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

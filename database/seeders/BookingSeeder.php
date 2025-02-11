<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Cabin;
use App\Models\User;
use App\Repositories\BookingRepository;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;
use App\Traits\BookingHandler;
use Illuminate\Support\Facades\Auth;
use Hidehalo\Nanoid\Client;
use Log;

class BookingSeeder extends Seeder
{
  use BookingHandler;

  protected BookingRepository $bookingRepository;

  public function __construct(BookingRepository $bookingRepository)
  {
    $this->bookingRepository = $bookingRepository;
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $client = new Client();

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    try {
      // Fetch 2 random available cabins for each cabin type (1, 2, and 3)
      $cabinType1 = Cabin::where('cabin_type_id', 1)->where('status', 'AVAILABLE')->inRandomOrder()->limit(2)->get();
      $cabinType2 = Cabin::where('cabin_type_id', 2)->where('status', 'AVAILABLE')->inRandomOrder()->limit(2)->get();
      $cabinType3 = Cabin::where('cabin_type_id', 3)->where('status', 'AVAILABLE')->inRandomOrder()->limit(2)->get();

      // Get enough unique customers (total 6 needed for bookings with cabins)
      //   $customersForBookings = User::where('organization_id', 1)
      // ->whereHas('roles', function ($query) {
      //     $query->where('name', 'Customer');
      // })->limit(6)->toSql();
      setPermissionsTeamId(1);

      $customersForBookings = User::role('Customer')->limit(6)->get();
      // Array of all selected cabins (2 for each type)
      $selectedCabins = $cabinType1->merge($cabinType2)->merge($cabinType3);
      foreach ($selectedCabins as $index => $cabin) {
        $customer = $customersForBookings[$index];
        $details = $customer->detail;
        Auth::login($customer);
        $bookingData = [
          'booking_request_id' => $client->formattedId('0123456789ABCDEFGHIJKLMNOPERSTUWXYZ', 10),
          'event_id' => 1,
          'payment_plan' => $index % 2 === 0 ? 'INSTALLMENTS' : 'PAY_IN_FULL',
          'customer_id' => $customer->id,
          'number_of_installments' => $index % 2 === 0 ? 3 : 4,
        ];

        $passengerData = [
          'confirmed_booked_email' => true,
          'lead_passenger' => true,
          'first_name' => $details->first_name,
          'address_first' => 'test',
          'address_second' => 'test',
          'last_name' => $details->last_name,
          'gender' => $details->gender,
          'dob' => $details->dob,
          'citizenship' => $details->citizenship,
          'city' => 'test',
          'state' => 'test',
          'postal_code' => 'test',
          'country' => 'test',
          'email' => 'test',
          'phone' => 'test',
          'emergency_c_name' => 'test',
          'emergency_c_phone' => 'test',
          'special_request' => 'test',
          'newsletter' => false,
          'travel_info' => false,
          'terms_n_cons' => false,
          'cabin_conf_accp' => false,
          'single_t_agreement' => false,
          'passenger_allocated_cost' => 0,
        ];
        $this->bookingRepository->createBooking($bookingData, $passengerData, $cabin);
        Auth::logout();
      }

      // Ensure no customer is repeated for bookings with cabins
      // foreach ($selectedCabins as $index => $cabin) {
      //   $customer = $customersForBookings[$index]; // Select a unique customer for each booking
      //   // // Create the booking with a unique customer_id
      //   $booking = Booking::factory()->create([
      //     "event_id" => 1,
      //     "payment_plan" => $index % 2 === 0 ? "INSTALLMENTS" : "PAY_IN_FULL",
      //     "customer_id" => $customer->id,
      //     "cabin_id" => $cabin->id,
      //   ]);
      // }
    } catch (\Exception $e) {
      //throw $th;
      Log::error($e->getMessage());
      dd($e->getMessage());
    }
  }
}

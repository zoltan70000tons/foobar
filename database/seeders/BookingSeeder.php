<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Cabin;
use App\Models\User;

class BookingSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // Fetch 2 random available cabins for each cabin type (1, 2, and 3)
    $cabinType1 = Cabin::where('cabin_type_id', 1)->where('status', 'AVAILABLE')->inRandomOrder()->limit(2)->get();
    $cabinType2 = Cabin::where('cabin_type_id', 2)->where('status', 'AVAILABLE')->inRandomOrder()->limit(2)->get();
    $cabinType3 = Cabin::where('cabin_type_id', 3)->where('status', 'AVAILABLE')->inRandomOrder()->limit(2)->get();

    // Get enough unique customers (total 6 needed for bookings with cabins)
    $customersForBookings = User::inRandomOrder()->limit(6)->get();

    // Array of all selected cabins (2 for each type)
    $selectedCabins = $cabinType1->merge($cabinType2)->merge($cabinType3);

    // Ensure no customer is repeated for bookings with cabins
    foreach ($selectedCabins as $index => $cabin) {
      $customer = $customersForBookings[$index]; // Select a unique customer for each booking

      // Create the booking with a unique customer_id
      $booking = Booking::factory()->create([
        'customer_id' => $customer->id,
        'cabin_id' => $cabin->id,
      ]);

      // Use the assignCabin() method to assign the cabin and handle inventory/status updates
      $booking->assignCabin($cabin);
    }
  }
}

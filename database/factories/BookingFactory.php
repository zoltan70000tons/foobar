<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
  protected $model = Booking::class;

  public function definition()
  {
    // Define a set of A-Z characters
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // Generate a random 4-character code
    $identifier_code = substr(str_shuffle($characters), 0, 4);

    return [
      'booking_code' => '0000_' . $identifier_code, // Generate a random booking code
      'customer_id' => Customer::factory(), // Placeholder, will be overridden in seeder
      'payment_plan' => $this->faker->randomElement(['pay_in_full', 'installments']), // Randomly decide if booking is paid in full or in installments
      'carbon_offset' => $this->faker->boolean, // Randomly decide if carbon offset is applied
      'cabin_id' => null, // Placeholder, will be overridden in seeder
      'self_assigned' => false, // False by default, indicating cabin was assigned by app
      'completed' => true, // Indicating bookings are completed
    ];
  }
}

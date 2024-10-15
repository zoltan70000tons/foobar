<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
  protected $model = Booking::class;

  public function definition()
  {
    return [      
      'booking_code' => null, // Placeholder, will be overridden in seeder
      'customer_id' => User::factory(), // Placeholder, will be overridden in seeder
      'payment_plan' => $this->faker->randomElement(['PAY_IN_FULL', '4_INSTALLMENTS', '3_INSTALLMENTS']), // Randomly select a payment plan
      'cabin_id' => null, // Placeholder, will be overridden in seeder
      'tags' => json_encode(['New']), // Default tag set to "New"
      'created_at' => now(),
      'updated_at' => now(),
    ];
  }
}
